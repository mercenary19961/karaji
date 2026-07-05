import ShopLayout from '@/layouts/shop-layout';
import { type DueTodayItem, type Shop, type ShopStats } from '@/types/shop';
import { Head, Link, router } from '@inertiajs/react';
import { Plus, Search } from 'lucide-react';
import { type FormEvent } from 'react';

interface Props {
    shop: Shop;
    stats: ShopStats;
    dueToday: DueTodayItem[];
}

export default function Dashboard({ shop, stats, dueToday }: Props) {
    // Demo: any search lands on the demo car until real lookup exists
    const search = (e: FormEvent) => {
        e.preventDefault();
        router.visit(route('shop.cars.show'));
    };

    return (
        <ShopLayout shop={shop}>
            <Head title="الرئيسية" />

            <form onSubmit={search} className="relative">
                <Search className="absolute start-4 top-1/2 size-5 -translate-y-1/2 text-muted-foreground" aria-hidden />
                <input
                    inputMode="numeric"
                    placeholder="رقم اللوحة أو الهاتف"
                    className="h-15 w-full rounded-2xl border-2 border-input bg-card ps-12 pe-4 text-[19px] text-foreground outline-none focus-visible:border-ring"
                />
            </form>

            <Link
                href={route('shop.visits.create')}
                className="flex h-16 items-center justify-center gap-2 rounded-2xl bg-cta text-[22px] font-extrabold text-cta-foreground shadow-lg shadow-cta/35"
            >
                <Plus className="size-6" aria-hidden />
                زيارة جديدة
            </Link>

            <div className="grid grid-cols-3 gap-2.5">
                <div className="rounded-2xl bg-card px-2.5 py-3.5 text-center shadow-sm">
                    <div className="text-[26px] font-extrabold text-primary">{stats.todayVisits}</div>
                    <div className="mt-1 text-sm text-muted-foreground">سيارات اليوم</div>
                </div>
                <div className="rounded-2xl bg-card px-2.5 py-3.5 text-center shadow-sm">
                    <div className="text-[26px] font-extrabold text-due-foreground">{stats.dueCount}</div>
                    <div className="mt-1 text-sm text-muted-foreground">مستحق التذكير</div>
                </div>
                <div className="rounded-2xl bg-card px-2.5 py-3.5 text-center shadow-sm">
                    <div className="text-[22px] font-extrabold text-success">
                        {stats.monthRevenue} <span className="text-[13px]">د.أ</span>
                    </div>
                    <div className="mt-1 text-sm text-muted-foreground">دخل الشهر</div>
                </div>
            </div>

            <div className="mt-1 flex items-center justify-between">
                <h2 className="text-lg font-extrabold">مستحق التواصل اليوم</h2>
                <Link
                    href={route('shop.reminders')}
                    className="flex min-h-12 items-center px-2 text-[15px] font-bold text-primary"
                >
                    عرض الكل ←
                </Link>
            </div>

            <div className="flex flex-col gap-2.5">
                {dueToday.map((item) => (
                    <Link
                        key={item.car + item.owner}
                        href={route('shop.reminders')}
                        className="flex items-center justify-between gap-2 rounded-2xl bg-card p-4 shadow-sm"
                    >
                        <div>
                            <div className="text-[17px] font-bold">
                                {item.car} — {item.owner}
                            </div>
                            <div className="mt-0.5 text-[15px] text-muted-foreground">{item.due}</div>
                        </div>
                        <span className="rounded-full bg-due px-3 py-1.5 text-sm font-bold whitespace-nowrap text-due-foreground">
                            {item.overdueLabel}
                        </span>
                    </Link>
                ))}
            </div>
        </ShopLayout>
    );
}
