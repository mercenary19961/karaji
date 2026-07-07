<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin IdeHelperUser
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * role/shop_id intentionally NOT fillable — assigned only by explicit
     * admin flows, never from request input.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'locale',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'avatar_path',
    ];

    /**
     * Expose the resolved avatar URL (not the raw path) to the frontend.
     *
     * @var list<string>
     */
    protected $appends = ['avatar_url'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Where this user belongs after login — their portal, never the scaffold
     * dashboard. Single source of truth for every post-auth redirect.
     */
    public function homeRoute(): string
    {
        return $this->isAdmin() ? route('admin.shops.index') : route('shop.dashboard');
    }

    /** Public URL of the profile picture, or null. */
    protected function avatarUrl(): Attribute
    {
        return Attribute::get(fn () => $this->avatar_path
            ? Storage::disk('public')->url($this->avatar_path)
            : null);
    }

    /** Store a new avatar (deletes the previous file first). */
    public function setAvatar(string $path): void
    {
        $this->clearAvatarFile();
        $this->forceFill(['avatar_path' => $path])->save();
    }

    public function removeAvatar(): void
    {
        if ($this->avatar_path === null) {
            return;
        }

        $this->clearAvatarFile();
        $this->forceFill(['avatar_path' => null])->save();
    }

    private function clearAvatarFile(): void
    {
        if ($this->avatar_path) {
            Storage::disk('public')->delete($this->avatar_path);
        }
    }
}
