import ShopLayout from '@/layouts/shop-layout';
import { useT } from '@/lib/i18n';
import { type SharedData } from '@/types';
import { type DashboardAnnouncement, type DueTodayItem, type Shop, type ShopStats } from '@/types/shop';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Megaphone, MessageCircle, Plus, Search, X } from 'lucide-react';
import { type FormEvent, useState } from 'react';

interface LostCustomer {
    owner: string;
    car: string;
    lastVisit: string;
    whatsapp: string;
}

interface Props {
    shop: Shop;
    stats: ShopStats;
    dueToday: DueTodayItem[];
    announcements: DashboardAnnouncement[];
    lostCustomers: LostCustomer[];
}

export default function Dashboard({ shop, stats, dueToday, announcements, lostCustomers }: Props) {
    const { flash } = usePage<SharedData>().props;
    const t = useT();
    const [q, setQ] = useState('');

    const search = (e: FormEvent) => {
        e.preventDefault();
        router.get(route('shop.cars.search'), { q });
    };

    return (
        <ShopLayout shop={shop}>
            <Head title={t('nav.home')} />

            {/* Desktop-only greeting (mobile shows the header instead) */}
            <div className="hidden md:block">
                <h1 className="text-2xl font-extrabold">{t('dash.greeting')}</h1>
                <p className="text-muted-foreground mt-1 text-[15px]">{t('dash.subtitle', { count: stats.dueCount })}</p>
            </div>

            {announcements.map((announcement) => (
                <div key={announcement.id} className="border-cta bg-due flex items-start gap-3 rounded-2xl border-2 p-4">
                    <Megaphone className="text-due-foreground mt-0.5 size-5 shrink-0" aria-hidden />
                    <div className="flex-1">
                        <div className="text-due-foreground text-[16px] font-extrabold">{announcement.title}</div>
                        <div className="text-due-foreground/85 mt-0.5 text-[15px]">{announcement.body}</div>
                    </div>
                    <button
                        type="button"
                        onClick={() => router.post(route('shop.announcements.dismiss', announcement.id), {}, { preserveScroll: true })}
                        aria-label={t('dash.dismiss')}
                        className="text-due-foreground/60 hover:text-due-foreground -my-1 -me-1 flex size-9 shrink-0 items-center justify-center rounded-lg"
                    >
                        <X className="size-5" aria-hidden />
                    </button>
                </div>
            ))}

            <div className="flex flex-col gap-3 md:flex-row">
                <form onSubmit={search} className="relative flex-1">
                    <Search className="text-muted-foreground absolute start-4 top-1/2 size-5 -translate-y-1/2" aria-hidden />
                    <input
                        inputMode="numeric"
                        placeholder={t('dash.search')}
                        value={q}
                        onChange={(e) => setQ(e.target.value)}
                        className="border-input bg-card text-foreground focus-visible:border-ring h-16 w-full rounded-2xl border-2 ps-12 pe-4 text-[19px] outline-none"
                    />
                </form>

                <Link
                    href={route('shop.entry')}
                    className="bg-cta text-cta-foreground shadow-cta/35 flex h-16 items-center justify-center gap-2 rounded-2xl px-6 text-[22px] font-extrabold shadow-lg md:w-auto"
                >
                    <Plus className="size-6" aria-hidden />
                    {t('dash.new_visit')}
                </Link>
            </div>
            {flash.error && <div className="text-destructive -mt-1 text-[15px] font-bold">{flash.error}</div>}

            <div className="grid grid-cols-3 gap-2.5 md:gap-4">
                <div className="bg-card rounded-2xl px-2.5 py-3.5 text-center shadow-sm md:px-4 md:py-5">
                    <div className="text-primary flex h-9 items-center justify-center text-[26px] font-extrabold md:h-11 md:text-[32px]">
                        {stats.todayVisits}
                    </div>
                    <div className="text-muted-foreground mt-1 text-sm">{t('dash.today_cars')}</div>
                </div>
                <div className="bg-card rounded-2xl px-2.5 py-3.5 text-center shadow-sm md:px-4 md:py-5">
                    <div className="text-due-foreground flex h-9 items-center justify-center text-[26px] font-extrabold md:h-11 md:text-[32px]">
                        {stats.dueCount}
                    </div>
                    <div className="text-muted-foreground mt-1 text-sm">{t('dash.due_count')}</div>
                </div>
                <div className="bg-card rounded-2xl px-2.5 py-3.5 text-center shadow-sm md:px-4 md:py-5">
                    <div className="text-success flex h-9 items-center justify-center text-[22px] font-extrabold md:h-11 md:text-[30px]">
                        <span>
                            {stats.monthRevenue} <span className="text-[13px]">{t('common.currency')}</span>
                        </span>
                    </div>
                    <div className="text-muted-foreground mt-1 text-sm">{t('dash.month_income')}</div>
                </div>
            </div>

            <div className="grid gap-4 md:grid-cols-[1.6fr_1fr] md:items-start md:gap-5">
                {/* Due-today list — mobile and desktop */}
                <div className="flex flex-col gap-2.5">
                    <div className="flex items-center justify-between">
                        <h2 className="text-lg font-extrabold">{t('dash.due_today')}</h2>
                        <Link href={route('shop.reminders')} className="text-primary flex min-h-12 items-center px-2 text-[15px] font-bold">
                            {t('dash.view_all')}
                        </Link>
                    </div>

                    {dueToday.map((item) => (
                        <Link
                            key={item.car + item.owner}
                            href={route('shop.reminders')}
                            className="bg-card flex items-center justify-between gap-2 rounded-2xl p-4 shadow-sm"
                        >
                            <div>
                                <div className="text-[17px] font-bold">
                                    {item.car} · {item.owner}
                                </div>
                                <div className="text-muted-foreground mt-0.5 text-[15px]">{item.due}</div>
                            </div>
                            <span className="bg-due text-due-foreground rounded-full px-3 py-1.5 text-sm font-bold whitespace-nowrap">
                                {item.overdueLabel}
                            </span>
                        </Link>
                    ))}
                    {dueToday.length === 0 && (
                        <div className="bg-card text-muted-foreground rounded-2xl p-5 text-center text-base">{t('dash.no_due')}</div>
                    )}
                </div>

                {/* "Customers you're losing" — desktop-only side panel */}
                <div className="hidden md:flex md:flex-col md:gap-2.5">
                    <h2 className="text-lg font-extrabold">
                        {t('dash.losing')} <span className="text-muted-foreground text-sm font-medium">{t('dash.losing_window')}</span>
                    </h2>
                    <div className="bg-card flex flex-col rounded-2xl p-2 shadow-sm">
                        {lostCustomers.map((customer) => (
                            <div
                                key={customer.owner + customer.car}
                                className="border-muted flex items-center justify-between gap-2 border-b p-2.5 last:border-b-0"
                            >
                                <div className="min-w-0">
                                    <div className="truncate text-[15px] font-bold">
                                        {customer.owner} · {customer.car}
                                    </div>
                                    <div className="text-muted-foreground text-[13px]">{customer.lastVisit}</div>
                                </div>
                                <a
                                    href={`https://wa.me/${customer.whatsapp}`}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    aria-label={t('common.whatsapp')}
                                    className="bg-success-soft text-success-soft-foreground flex size-10 shrink-0 items-center justify-center rounded-xl"
                                >
                                    <MessageCircle className="size-5" aria-hidden />
                                </a>
                            </div>
                        ))}
                        {lostCustomers.length === 0 && <div className="text-muted-foreground p-4 text-center text-sm">{t('dash.no_losing')}</div>}
                    </div>
                </div>
            </div>
        </ShopLayout>
    );
}
