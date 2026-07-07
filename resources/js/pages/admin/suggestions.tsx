import AdminLayout from '@/layouts/admin-layout';
import { type AdminSuggestion } from '@/types/admin';
import { Head, router } from '@inertiajs/react';

interface Props {
    suggestions: AdminSuggestion[];
}

export default function Suggestions({ suggestions }: Props) {
    const setStatus = (id: number, status: 'open' | 'reviewed') =>
        router.put(route('admin.suggestions.update', id), { status }, { preserveScroll: true });

    const openCount = suggestions.filter((s) => s.status === 'open').length;

    return (
        <AdminLayout>
            <Head title="Suggestions" />

            <div>
                <h1 className="text-2xl font-extrabold">Suggestions</h1>
                <p className="text-muted-foreground mt-1 text-[15px]">
                    Feature ideas and requests from shop owners. {openCount} open · {suggestions.length} total.
                </p>
            </div>

            <div className="flex flex-col gap-3">
                {suggestions.map((suggestion) => (
                    <div
                        key={suggestion.id}
                        className="border-border bg-card flex flex-wrap items-start justify-between gap-3 rounded-2xl border p-5"
                    >
                        <div className="min-w-0 flex-1">
                            <div className="flex items-center gap-2">
                                <span className="text-primary text-sm font-extrabold">{suggestion.shop}</span>
                                <span className="text-muted-foreground text-[13px]">{suggestion.date}</span>
                                <span
                                    className={`rounded-full px-2 py-0.5 text-[11px] font-extrabold ${
                                        suggestion.status === 'reviewed'
                                            ? 'bg-success-soft text-success-soft-foreground'
                                            : 'bg-due text-due-foreground'
                                    }`}
                                >
                                    {suggestion.status === 'reviewed' ? 'Reviewed' : 'Open'}
                                </span>
                            </div>
                            <p className="mt-2 text-[15px]">{suggestion.body}</p>
                        </div>
                        {suggestion.status === 'open' ? (
                            <button
                                type="button"
                                onClick={() => setStatus(suggestion.id, 'reviewed')}
                                className="border-success/40 bg-card text-success-soft-foreground h-11 shrink-0 cursor-pointer rounded-lg border px-4 text-sm font-bold"
                            >
                                Mark reviewed
                            </button>
                        ) : (
                            <button
                                type="button"
                                onClick={() => setStatus(suggestion.id, 'open')}
                                className="border-input bg-card text-muted-foreground h-11 shrink-0 cursor-pointer rounded-lg border px-4 text-sm font-bold"
                            >
                                Reopen
                            </button>
                        )}
                    </div>
                ))}
                {suggestions.length === 0 && (
                    <div className="border-border bg-card text-muted-foreground rounded-2xl border p-8 text-center text-[15px]">
                        No suggestions yet.
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
