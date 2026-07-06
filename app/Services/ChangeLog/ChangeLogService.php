<?php

namespace App\Services\ChangeLog;

use App\Models\ActivityLog;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Admin change log + revert, v2 — ported from retab-stores (which was designed
 * against the failure catalog of Sky Amman's snapshot-based service):
 *
 *  1. UPDATE entries snapshot ONLY the fields that changed (never the full
 *     record), so reverting an old edit can't clobber newer edits to other fields.
 *  2. Reverting an update CONFLICT-CHECKS each field (current value vs the
 *     entry's new_data) and refuses with the diverged field list instead of
 *     blindly writing over someone's later change.
 *  3. Every revert runs in a transaction and returns a RevertResult with an
 *     honest reason — never "success" that did nothing.
 *  4. A revert writes a NEW entry linked via reverts_log_id, then stamps the
 *     original. History stays complete, and redo = reverting the mirror entry —
 *     same machinery.
 *
 * Karaji v1: subscriptions are the only revertable subject. Created/deleted
 * logging stays audit-only until more admin CRUD exists.
 */
class ChangeLogService
{
    /** Metadata never snapshotted, diffed, or written back. */
    private const SKIP_KEYS = ['id', 'shop_id', 'created_at', 'updated_at'];

    /** subject_type => actions that may be reverted. Everything else is audit-only. */
    private const REVERTABLE = [
        Subscription::class => [ActivityLog::ACTION_UPDATED],
    ];

    /** subject_type => section label for the admin list. */
    public const SUBJECT_LABELS = [
        Subscription::class => 'Subscription',
    ];

    // ── Logging ─────────────────────────────────────────────────────────────

    public function logCreated(Model $subject, ?string $label = null): ActivityLog
    {
        return $this->record([
            'action' => ActivityLog::ACTION_CREATED,
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'shop_id' => $subject->getAttribute('shop_id'),
            'new_data' => $this->snapshot($subject->attributesToArray()),
            'label' => $label,
        ]);
    }

    /**
     * Log an update. `$before` is `attributesToArray()` captured BEFORE the save;
     * only the fields the save actually changed are snapshotted. Returns null
     * when nothing changed (no-op guard — no log noise, nothing to revert).
     */
    public function logUpdated(Model $subject, array $before, ?string $label = null, ?int $revertsLogId = null): ?ActivityLog
    {
        $keys = array_values(array_diff(array_keys($subject->getChanges()), self::SKIP_KEYS));
        if ($keys === []) {
            return null;
        }

        $after = $subject->attributesToArray();

        return $this->record([
            'action' => ActivityLog::ACTION_UPDATED,
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'shop_id' => $subject->getAttribute('shop_id'),
            'old_data' => Arr::only($before, $keys),
            'new_data' => Arr::only($after, $keys),
            'label' => $label,
            'reverts_log_id' => $revertsLogId,
        ]);
    }

    /** Log a delete (full snapshot in old_data). Audit-only in Karaji v1. */
    public function logDeleted(Model $subject, ?string $label = null): ActivityLog
    {
        return $this->record([
            'action' => ActivityLog::ACTION_DELETED,
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'shop_id' => $subject->getAttribute('shop_id'),
            'old_data' => $this->snapshot($subject->attributesToArray()),
            'label' => $label,
        ]);
    }

    // ── Revert ──────────────────────────────────────────────────────────────

    /** Whether an entry may be reverted (action + subject matrix, not yet reverted). */
    public function revertable(ActivityLog $log): bool
    {
        if ($log->isReverted()) {
            return false;
        }

        $allowed = self::REVERTABLE[$log->subject_type] ?? null;

        return $allowed !== null && in_array($log->action, $allowed, true);
    }

    /**
     * Revert an entry. Never partial: either the whole revert applies (and a
     * mirror entry is written) or nothing changes and the result says why.
     */
    public function revert(ActivityLog $log): RevertResult
    {
        if ($log->isReverted()) {
            return RevertResult::fail(RevertResult::REASON_ALREADY_REVERTED);
        }

        if (! $this->revertable($log)) {
            return RevertResult::fail(RevertResult::REASON_NOT_REVERTABLE);
        }

        /** @var class-string<Model> $class */
        $class = $log->subject_type;
        $subject = $class::query()->find($log->subject_id);

        if ($subject === null) {
            return RevertResult::fail(RevertResult::REASON_SUBJECT_MISSING);
        }

        try {
            return $this->revertUpdate($log, $subject);
        } catch (Throwable $e) {
            report($e); // e.g. a unique collision on a written-back value

            return RevertResult::fail(RevertResult::REASON_FAILED);
        }
    }

    /**
     * Field-scoped write-back with conflict detection: every field must still
     * hold the value this entry wrote (new_data); otherwise a later edit owns it
     * and we refuse rather than clobber.
     */
    private function revertUpdate(ActivityLog $log, Model $subject): RevertResult
    {
        $old = $log->old_data ?? [];
        if ($old === []) {
            return RevertResult::fail(RevertResult::REASON_FAILED);
        }

        $current = $subject->attributesToArray();
        $new = $log->new_data ?? [];

        $conflicts = [];
        foreach (array_keys($old) as $key) {
            if ($this->normalize($current[$key] ?? null) !== $this->normalize($new[$key] ?? null)) {
                $conflicts[] = $this->humanize($key);
            }
        }
        if ($conflicts !== []) {
            return RevertResult::conflict($conflicts);
        }

        return DB::transaction(function () use ($log, $subject, $old) {
            $before = $subject->attributesToArray();
            $subject->fill($old);
            $subject->save();

            $mirror = $this->logUpdated($subject, $before, $this->revertLabel($log), $log->id);
            $this->stamp($log);

            return RevertResult::ok($mirror);
        });
    }

    // ── Display ─────────────────────────────────────────────────────────────

    /**
     * Human-readable field changes for the admin list: [{label, old, new}].
     */
    public function diff(ActivityLog $log): array
    {
        $old = $log->old_data ?? [];
        $new = $log->new_data ?? [];
        $keys = array_diff(array_unique([...array_keys($old), ...array_keys($new)]), self::SKIP_KEYS);

        $changes = [];
        foreach ($keys as $key) {
            $a = $this->normalize($old[$key] ?? null);
            $b = $this->normalize($new[$key] ?? null);
            if ($a !== $b) {
                $changes[] = [
                    'label' => $this->humanize($key),
                    'old' => $this->pretty($old[$key] ?? null),
                    'new' => $this->pretty($new[$key] ?? null),
                ];
            }
        }

        return $changes;
    }

    public function sectionLabel(ActivityLog $log): string
    {
        return self::SUBJECT_LABELS[$log->subject_type]
            ?? ($log->subject_type ? class_basename($log->subject_type) : 'System');
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    /** @param array<string, mixed> $attributes */
    private function record(array $attributes): ActivityLog
    {
        return ActivityLog::create($attributes + ['user_id' => Auth::id()]);
    }

    private function stamp(ActivityLog $log): void
    {
        $log->update(['reverted_at' => now(), 'reverted_by' => Auth::id()]);
    }

    private function revertLabel(ActivityLog $log): string
    {
        return 'Undo: '.($log->label ?? $this->sectionLabel($log).' change');
    }

    /** @return array<string, mixed> */
    private function snapshot(array $attributes): array
    {
        return Arr::except($attributes, self::SKIP_KEYS);
    }

    /** Canonical string form for equality checks (survives the JSON round-trip). */
    private function normalize(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if (is_array($value)) {
            return json_encode($value) ?: '';
        }

        return (string) ($value ?? '');
    }

    private function pretty(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }
        if (is_array($value)) {
            $value = json_encode($value) ?: '';
        }

        $value = trim((string) ($value ?? ''));
        if ($value === '') {
            return '—';
        }

        return mb_strlen($value) <= 80 ? $value : mb_substr($value, 0, 80).'…';
    }

    private function humanize(string $key): string
    {
        return ucwords(str_replace('_', ' ', $key));
    }
}
