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
                    Shops <span className="text-muted-foreground text-[15px] font-medium">({visible.length})</span>
                </h1>
                <input
                    value={query}
                    onChange={(e) => setQuery(e.target.value)}
                    placeholder="Search shops…"
                    className="border-input bg-card focus-visible:border-ring h-11 w-64 rounded-xl border px-3.5 text-[15px] outline-none"
                />
            </div>

            <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                {visible.map((shop) => (
                    <Link
                        key={shop.id}
                        href={route('admin.shops.show')}
                        className="border-border bg-card hover:border-primary flex flex-col gap-2.5 rounded-2xl border p-4.5 shadow-sm transition-colors"
                    >
                        <div className="flex items-start justify-between gap-2">
                            <div>
                                <div className="text-[17px] font-extrabold">{shop.name}</div>
                                <div className="text-muted-foreground mt-0.5 text-sm">{shop.area}</div>
                            </div>
                            <span className={`rounded-full px-2.5 py-1 text-xs font-extrabold whitespace-nowrap ${badgeClasses[shop.status]}`}>
                                {shop.status}
                            </span>
                        </div>
                        <div className="text-muted-foreground flex justify-between text-sm">
                            <span>
                                <b className="text-foreground">{shop.visits}</b> visits this month
                            </span>
                            <span>{shop.lastActive}</span>
                        </div>
                    </Link>
                ))}
            </div>

            {visible.length === 0 && (
                <div className="border-border bg-card text-muted-foreground rounded-2xl border p-8 text-center">No shops match “{query.trim()}”.</div>
            )}
        </AdminLayout>
    );
}
