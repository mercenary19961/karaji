import AdminLayout from '@/layouts/admin-layout';
import { type ShopDetail, type SubscriptionStatus } from '@/types/admin';
import { Head, Link, router } from '@inertiajs/react';

const statusBadge: Record<SubscriptionStatus, string> = {
    active: 'bg-success-soft text-success-soft-foreground',
    trial: 'bg-secondary text-secondary-foreground',
    suspended: 'bg-destructive/10 text-destructive',
};

const statusLabels: Record<SubscriptionStatus, string> = {
    active: 'Active',
    trial: 'Trial',
    suspended: 'Suspended',
};

interface Props {
    shop: ShopDetail;
}

export default function ShopDetailPage({ shop }: Props) {
    const subscription = shop.subscription;

    const updateSubscription = (payload: { plan?: string; status?: SubscriptionStatus }) =>
        router.put(route('admin.shops.subscription', shop.id), payload, { preserveScroll: true });

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
                <button
                    type="button"
                    onClick={() => router.post(route('admin.shops.impersonate', shop.id))}
                    className="bg-primary text-primary-foreground flex h-12 cursor-pointer items-center rounded-xl px-5 text-[15px] font-bold"
                >
                    Login as shop →
                </button>
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

                    {!subscription && <div className="text-muted-foreground text-sm">No subscription yet.</div>}

                    {subscription && (
                        <>
                            <div className="flex items-center justify-between text-[15px]">
                                <span className="text-muted-foreground">Status</span>
                                <span className={`rounded-full px-3 py-1 text-[13px] font-extrabold ${statusBadge[subscription.status]}`}>
                                    {statusLabels[subscription.status]}
                                </span>
                            </div>
                            <div className="flex items-center justify-between gap-3 text-[15px]">
                                <span className="text-muted-foreground">Plan</span>
                                <select
                                    value={subscription.plan}
                                    onChange={(e) => updateSubscription({ plan: e.target.value })}
                                    className="border-input bg-card focus-visible:border-ring h-11 rounded-lg border px-2.5 text-sm outline-none"
                                >
                                    {subscription.plans.map((plan) => (
                                        <option key={plan.key} value={plan.key}>
                                            {plan.label}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            {subscription.renewsAt && (
                                <div className="flex items-center justify-between text-[15px]">
                                    <span className="text-muted-foreground">Renews</span>
                                    <span className="font-bold">{subscription.renewsAt}</span>
                                </div>
                            )}
                            {subscription.trialEndsAt && (
                                <div className="flex items-center justify-between text-[15px]">
                                    <span className="text-muted-foreground">Trial ends</span>
                                    <span className="font-bold">{subscription.trialEndsAt}</span>
                                </div>
                            )}
                            <div className="mt-1 flex gap-2.5">
                                <button
                                    type="button"
                                    onClick={() => router.post(route('admin.shops.subscription.extend', shop.id), {}, { preserveScroll: true })}
                                    className="border-input bg-card text-primary h-11 flex-1 cursor-pointer rounded-lg border text-sm font-bold"
                                >
                                    Extend trial
                                </button>
                                {subscription.status === 'suspended' ? (
                                    <button
                                        type="button"
                                        onClick={() => updateSubscription({ status: 'active' })}
                                        className="border-success/40 bg-card text-success-soft-foreground h-11 flex-1 cursor-pointer rounded-lg border text-sm font-bold"
                                    >
                                        Activate
                                    </button>
                                ) : (
                                    <button
                                        type="button"
                                        onClick={() => updateSubscription({ status: 'suspended' })}
                                        className="border-destructive/30 bg-card text-destructive h-11 flex-1 cursor-pointer rounded-lg border text-sm font-bold"
                                    >
                                        Suspend
                                    </button>
                                )}
                            </div>
                        </>
                    )}
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
                                    {entry.undone ? <s className="text-muted-foreground">{entry.text}</s> : entry.text}
                                    {entry.undoable && (
                                        <button
                                            type="button"
                                            onClick={() => router.post(route('admin.activity.undo', entry.id), {}, { preserveScroll: true })}
                                            className="bg-due text-due-foreground h-8 cursor-pointer rounded-lg px-3 text-[13px] font-extrabold"
                                        >
                                            Undo
                                        </button>
                                    )}
                                    {entry.undone && <span className="text-success text-[13px] font-bold">Undone ✓</span>}
                                </span>
                                <span className="text-muted-foreground whitespace-nowrap">{entry.at}</span>
                            </div>
                        ))}
                        {shop.activity.length === 0 && <div className="text-muted-foreground text-sm">No admin activity for this shop yet.</div>}
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
