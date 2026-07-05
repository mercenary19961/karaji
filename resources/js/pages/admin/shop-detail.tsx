import AdminLayout from '@/layouts/admin-layout';
import { type ShopDetail, type SubscriptionStatus } from '@/types/admin';
import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';

const statusBadge: Record<SubscriptionStatus, string> = {
    Active: 'bg-success-soft text-success-soft-foreground',
    Trial: 'bg-secondary text-secondary-foreground',
    Suspended: 'bg-destructive/10 text-destructive',
};

interface Props {
    shop: ShopDetail;
}

export default function ShopDetailPage({ shop }: Props) {
    const [plan, setPlan] = useState(shop.subscription.plan);
    // Demo: undo is client-side only until ActivityLogService is ported
    const [undone, setUndone] = useState<Record<string, boolean>>({});

    return (
        <AdminLayout>
            <Head title={shop.name} />

            <div className="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <Link href={route('admin.shops.index')} className="text-primary text-sm font-bold">
                        ← All shops
                    </Link>
                    <h1 className="mt-1 text-2xl font-extrabold">
                        {shop.name} <span className="text-muted-foreground text-[15px] font-medium">— {shop.area}</span>
                    </h1>
                </div>
                <Link
                    href={route('shop.dashboard')}
                    className="bg-primary text-primary-foreground flex h-12 items-center rounded-xl px-5 text-[15px] font-bold"
                >
                    Login as shop →
                </Link>
            </div>

            <div className="grid grid-cols-2 gap-4 lg:grid-cols-4">
                {shop.stats.map((stat, i) => (
                    <div key={stat.label} className="border-border bg-card rounded-2xl border p-4">
                        <div className={`text-[26px] font-extrabold ${i === shop.stats.length - 1 ? 'text-success' : 'text-primary'}`}>
                            {stat.value}
                        </div>
                        <div className="text-muted-foreground text-sm">{stat.label}</div>
                    </div>
                ))}
            </div>

            <div className="grid grid-cols-1 gap-5 lg:grid-cols-[1fr_1.4fr]">
                <div className="border-border bg-card flex flex-col gap-3.5 rounded-2xl border p-5">
                    <h2 className="text-base font-extrabold">Subscription</h2>
                    <div className="flex items-center justify-between text-[15px]">
                        <span className="text-muted-foreground">Status</span>
                        <span className={`rounded-full px-3 py-1 text-[13px] font-extrabold ${statusBadge[shop.subscription.status]}`}>
                            {shop.subscription.status}
                        </span>
                    </div>
                    <div className="flex items-center justify-between gap-3 text-[15px]">
                        <span className="text-muted-foreground">Plan</span>
                        <select
                            value={plan}
                            onChange={(e) => setPlan(e.target.value)}
                            className="border-input bg-card focus-visible:border-ring h-11 rounded-lg border px-2.5 text-sm outline-none"
                        >
                            {shop.subscription.plans.map((p) => (
                                <option key={p} value={p}>
                                    {p}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div className="flex items-center justify-between text-[15px]">
                        <span className="text-muted-foreground">Renews</span>
                        <span className="font-bold">{shop.subscription.renewsAt}</span>
                    </div>
                    <div className="mt-1 flex gap-2.5">
                        <button type="button" className="border-input bg-card text-primary h-11 flex-1 rounded-lg border text-sm font-bold">
                            Extend trial
                        </button>
                        <button
                            type="button"
                            className="border-destructive/30 bg-card text-destructive h-11 flex-1 rounded-lg border text-sm font-bold"
                        >
                            Suspend
                        </button>
                    </div>
                </div>

                <div className="border-border bg-card rounded-2xl border p-5">
                    <h2 className="mb-3 text-base font-extrabold">Recent activity</h2>
                    <div className="flex flex-col">
                        {shop.activity.map((entry, i) => (
                            <div
                                key={entry.id}
                                className={`flex items-center justify-between gap-3 py-2.5 text-sm ${
                                    i < shop.activity.length - 1 ? 'border-border border-b' : ''
                                }`}
                            >
                                <span className="flex items-center gap-2.5">
                                    {undone[entry.id] ? <s className="text-muted-foreground">{entry.text}</s> : entry.text}
                                    {entry.undoable && !undone[entry.id] && (
                                        <button
                                            type="button"
                                            onClick={() => setUndone((u) => ({ ...u, [entry.id]: true }))}
                                            className="bg-due text-due-foreground h-8 rounded-lg px-3 text-[13px] font-extrabold"
                                        >
                                            Undo
                                        </button>
                                    )}
                                    {undone[entry.id] && <span className="text-success text-[13px] font-bold">Undone ✓</span>}
                                </span>
                                <span className="text-muted-foreground whitespace-nowrap">{entry.at}</span>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
