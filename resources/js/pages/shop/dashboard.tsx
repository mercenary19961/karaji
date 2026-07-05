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
                <Search className="text-muted-foreground absolute start-4 top-1/2 size-5 -translate-y-1/2" aria-hidden />
                <input
                    inputMode="numeric"
                    placeholder="رقم اللوحة أو الهاتف"
                    className="border-input bg-card text-foreground focus-visible:border-ring h-15 w-full rounded-2xl border-2 ps-12 pe-4 text-[19px] outline-none"
                />
            </form>

            <Link
                href={route('shop.visits.create')}
                className="bg-cta text-cta-foreground shadow-cta/35 flex h-16 items-center justify-center gap-2 rounded-2xl text-[22px] font-extrabold shadow-lg"
            >
                <Plus className="size-6" aria-hidden />
                زيارة جديدة
            </Link>

            <div className="grid grid-cols-3 gap-2.5">
                <div className="bg-card rounded-2xl px-2.5 py-3.5 text-center shadow-sm">
                    <div className="text-primary text-[26px] font-extrabold">{stats.todayVisits}</div>
                    <div className="text-muted-foreground mt-1 text-sm">سيارات اليوم</div>
                </div>
                <div className="bg-card rounded-2xl px-2.5 py-3.5 text-center shadow-sm">
                    <div className="text-due-foreground text-[26px] font-extrabold">{stats.dueCount}</div>
                    <div className="text-muted-foreground mt-1 text-sm">مستحق التذكير</div>
                </div>
                <div className="bg-card rounded-2xl px-2.5 py-3.5 text-center shadow-sm">
                    <div className="text-success text-[22px] font-extrabold">
                        {stats.monthRevenue} <span className="text-[13px]">د.أ</span>
                    </div>
                    <div className="text-muted-foreground mt-1 text-sm">دخل الشهر</div>
                </div>
            </div>

            <div className="mt-1 flex items-center justify-between">
                <h2 className="text-lg font-extrabold">مستحق التواصل اليوم</h2>
                <Link href={route('shop.reminders')} className="text-primary flex min-h-12 items-center px-2 text-[15px] font-bold">
                    عرض الكل ←
                </Link>
            </div>

            <div className="flex flex-col gap-2.5">
                {dueToday.map((item) => (
                    <Link
                        key={item.car + item.owner}
                        href={route('shop.reminders')}
                        className="bg-card flex items-center justify-between gap-2 rounded-2xl p-4 shadow-sm"
                    >
                        <div>
                            <div className="text-[17px] font-bold">
                                {item.car} — {item.owner}
                            </div>
                            <div className="text-muted-foreground mt-0.5 text-[15px]">{item.due}</div>
                        </div>
                        <span className="bg-due text-due-foreground rounded-full px-3 py-1.5 text-sm font-bold whitespace-nowrap">
                            {item.overdueLabel}
                        </span>
                    </Link>
                ))}
            </div>
        </ShopLayout>
    );
}
