import AdminLayout from '@/layouts/admin-layout';
import { type ShopListItem, type SubscriptionStatus } from '@/types/admin';
import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';

const badgeClasses: Record<SubscriptionStatus, string> = {
    Active: 'bg-success-soft text-success-soft-foreground',
    Trial: 'bg-secondary text-secondary-foreground',
    Suspended: 'bg-destructive/10 text-destructive',
};

interface Props {
    shops: ShopListItem[];
}

export default function Shops({ shops }: Props) {
    const [query, setQuery] = useState('');

    const q = query.trim().toLowerCase();
    const visible = q === '' ? shops : shops.filter((s) => `${s.name} ${s.area}`.toLowerCase().includes(q));

    return (
        <AdminLayout>
            <Head title="Shops" />

            <div className="flex flex-wrap items-center justify-between gap-3">
                <h1 className="text-[22px] font-extrabold">
                    Shops <span className="text-[15px] font-medium text-muted-foreground">({visible.length})</span>
                </h1>
                <input
                    value={query}
                    onChange={(e) => setQuery(e.target.value)}
                    placeholder="Search shops…"
                    className="h-11 w-64 rounded-xl border border-input bg-card px-3.5 text-[15px] outline-none focus-visible:border-ring"
                />
            </div>

            <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                {visible.map((shop) => (
                    <Link
                        key={shop.id}
                        href={route('admin.shops.show')}
                        className="flex flex-col gap-2.5 rounded-2xl border border-border bg-card p-4.5 shadow-sm transition-colors hover:border-primary"
                    >
                        <div className="flex items-start justify-between gap-2">
                            <div>
                                <div className="text-[17px] font-extrabold">{shop.name}</div>
                                <div className="mt-0.5 text-sm text-muted-foreground">{shop.area}</div>
                            </div>
                            <span className={`rounded-full px-2.5 py-1 text-xs font-extrabold whitespace-nowrap ${badgeClasses[shop.status]}`}>
                                {shop.status}
                            </span>
                        </div>
                        <div className="flex justify-between text-sm text-muted-foreground">
                            <span>
                                <b className="text-foreground">{shop.visits}</b> visits this month
                            </span>
                            <span>{shop.lastActive}</span>
                        </div>
                    </Link>
                ))}
            </div>

            {visible.length === 0 && (
                <div className="rounded-2xl border border-border bg-card p-8 text-center text-muted-foreground">
                    No shops match “{query.trim()}”.
                </div>
            )}
        </AdminLayout>
    );
}
