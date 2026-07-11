import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import ShopLayout from '@/layouts/shop-layout';
import { useT } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import { type Analytics, type Shop } from '@/types/shop';
import { Head, router } from '@inertiajs/react';
import { ChevronDown, ChevronLeft, ChevronRight, MessageCircle } from 'lucide-react';
import { useState } from 'react';

interface Props {
    shop: Shop;
    analytics: Analytics;
}

// SVG chart geometry (viewBox units). `top` leaves headroom for the value
// labels; `labelGap` is the breathing room between the bars and the axis numbers.
const CHART = { width: 340, height: 152, top: 26, barArea: 92, barWidth: 30, labelGap: 24 };

export default function AnalyticsPage({ shop, analytics }: Props) {
    const t = useT();
    const { months, topServices, lostCustomers, selected, max, monthNames } = analytics;

    const [pickerOpen, setPickerOpen] = useState(false);
    const [viewYear, setViewYear] = useState(selected.year);

    const maxVisits = Math.max(...months.map((m) => m.visits), 1);
    // The window ends at the selected month, so the last bar is the active one
    const activeIndex = months.length - 1;
    const selectedVisits = months[activeIndex]?.visits ?? 0;
    const atMax = selected.year === max.year && selected.month === max.month;
    const minYear = max.year - 5;

    // Derived chart layout
    const slot = CHART.width / months.length;
    const baseline = CHART.top + CHART.barArea;
    const labelY = baseline + CHART.labelGap;

    const goToMonth = (year: number, month: number) => {
        setPickerOpen(false);
        router.get(route('shop.analytics'), { month: `${year}-${month}` }, { preserveScroll: true, preserveState: false, only: ['analytics'] });
    };

    // Step one calendar month, letting the Date roll the year over at the boundaries
    const shift = (dir: number) => {
        const d = new Date(selected.year, selected.month - 1 + dir, 1);
        goToMonth(d.getFullYear(), d.getMonth() + 1);
    };

    const openPicker = () => {
        setViewYear(selected.year);
        setPickerOpen(true);
    };

    return (
        <ShopLayout shop={shop}>
            <Head title={t('stats.title')} />

            <div className="bg-card flex items-center justify-between rounded-2xl p-1.5 shadow-sm">
                <button
                    type="button"
                    onClick={() => shift(-1)}
                    aria-label={t('stats.prev_month')}
                    className="bg-secondary text-primary flex size-13 items-center justify-center rounded-xl"
                >
                    <ChevronRight className="size-6 ltr:-scale-x-100" aria-hidden />
                </button>
                <button
                    type="button"
                    onClick={openPicker}
                    aria-label={t('stats.pick_month')}
                    className="hover:bg-secondary flex items-center gap-1.5 rounded-xl px-3 py-2 text-lg font-extrabold transition-colors"
                >
                    {monthNames[selected.month - 1]} {selected.year}
                    <ChevronDown className="text-muted-foreground size-4" aria-hidden />
                </button>
                <button
                    type="button"
                    onClick={() => shift(1)}
                    disabled={atMax}
                    aria-label={t('stats.next_month')}
                    className="bg-secondary text-primary flex size-13 items-center justify-center rounded-xl disabled:opacity-40"
                >
                    <ChevronLeft className="size-6 ltr:-scale-x-100" aria-hidden />
                </button>
            </div>

            <Dialog open={pickerOpen} onOpenChange={setPickerOpen}>
                <DialogContent className="max-w-xs gap-4 rounded-2xl">
                    <DialogHeader>
                        <DialogTitle className="text-center">{t('stats.pick_month')}</DialogTitle>
                    </DialogHeader>

                    <div className="flex items-center justify-between">
                        <button
                            type="button"
                            onClick={() => setViewYear((y) => y - 1)}
                            disabled={viewYear <= minYear}
                            aria-label={t('stats.prev_year')}
                            className="bg-secondary text-primary flex size-11 items-center justify-center rounded-xl disabled:opacity-40"
                        >
                            <ChevronRight className="size-5 ltr:-scale-x-100" aria-hidden />
                        </button>
                        <div className="text-lg font-extrabold">{viewYear}</div>
                        <button
                            type="button"
                            onClick={() => setViewYear((y) => y + 1)}
                            disabled={viewYear >= max.year}
                            aria-label={t('stats.next_year')}
                            className="bg-secondary text-primary flex size-11 items-center justify-center rounded-xl disabled:opacity-40"
                        >
                            <ChevronLeft className="size-5 ltr:-scale-x-100" aria-hidden />
                        </button>
                    </div>

                    <div className="grid grid-cols-3 gap-2">
                        {monthNames.map((name, i) => {
                            const month = i + 1;
                            const isFuture = viewYear > max.year || (viewYear === max.year && month > max.month);
                            const isSelected = viewYear === selected.year && month === selected.month;

                            return (
                                <button
                                    key={month}
                                    type="button"
                                    disabled={isFuture}
                                    onClick={() => goToMonth(viewYear, month)}
                                    className={cn(
                                        'flex h-14 flex-col items-center justify-center gap-0.5 rounded-xl text-sm font-bold transition-colors',
                                        isSelected ? 'bg-primary text-primary-foreground' : 'bg-secondary text-foreground hover:bg-secondary/70',
                                        isFuture && 'cursor-not-allowed opacity-30',
                                    )}
                                >
                                    <span>{name}</span>
                                    <span className="text-xs font-semibold opacity-60">{month}</span>
                                </button>
                            );
                        })}
                    </div>
                </DialogContent>
            </Dialog>

            <div className="grid gap-4 md:grid-cols-2 md:items-start">
                <div className="bg-card rounded-[18px] p-4 shadow-sm">
                    <div className="mb-4 flex items-baseline justify-between gap-2">
                        <h2 className="text-[17px] font-extrabold">{t('stats.monthly')}</h2>
                        <div className="text-muted-foreground text-sm font-medium">
                            {monthNames[selected.month - 1]}
                            <span className="text-primary px-1 font-extrabold">·</span>
                            <span className="text-primary font-extrabold">{selectedVisits}</span>
                        </div>
                    </div>
                    <svg viewBox={`0 0 ${CHART.width} ${CHART.height}`} className="block h-auto w-full" role="img" aria-label={t('stats.chart_aria')}>
                        {months.map((month, i) => {
                            const active = i === activeIndex;
                            const barHeight = Math.round((month.visits / maxVisits) * CHART.barArea);
                            const cx = slot * i + slot / 2;
                            const bx = cx - CHART.barWidth / 2;
                            const by = baseline - barHeight;

                            return (
                                <g key={`${month.year}-${month.month}`}>
                                    {/* faint full-height column so an empty month reads as a real "0", not a gap */}
                                    <rect
                                        x={bx}
                                        y={CHART.top}
                                        width={CHART.barWidth}
                                        height={CHART.barArea}
                                        rx={8}
                                        fill="var(--primary)"
                                        opacity={0.06}
                                    />
                                    {barHeight > 0 && (
                                        <rect
                                            x={bx}
                                            y={by}
                                            width={CHART.barWidth}
                                            height={barHeight}
                                            rx={6}
                                            fill="var(--primary)"
                                            opacity={active ? 1 : 0.28}
                                        />
                                    )}
                                    <text
                                        x={cx}
                                        y={by - 6}
                                        fontSize={active ? 13 : 11}
                                        fontWeight={active ? 800 : 600}
                                        fill={active ? 'var(--primary)' : 'var(--muted-foreground)'}
                                        textAnchor="middle"
                                    >
                                        {month.visits}
                                    </text>
                                    <text
                                        x={cx}
                                        y={labelY}
                                        fontSize={active ? 12 : 11}
                                        fontWeight={active ? 800 : 500}
                                        fill={active ? 'var(--primary)' : 'var(--muted-foreground)'}
                                        textAnchor="middle"
                                    >
                                        {month.month}
                                    </text>
                                </g>
                            );
                        })}
                    </svg>
                </div>

                <div className="bg-card rounded-[18px] p-4 shadow-sm">
                    <h2 className="mb-3 text-[17px] font-extrabold">{t('stats.top_services')}</h2>
                    <div className="flex flex-col gap-2.5">
                        {topServices.map((service) => (
                            <div key={service.label} className="flex justify-between text-base">
                                <span>{service.label}</span>
                                <b className="text-primary">{service.count}</b>
                            </div>
                        ))}
                        {topServices.length === 0 && <div className="text-muted-foreground text-base">{t('stats.no_services')}</div>}
                    </div>
                </div>
            </div>

            <div className="bg-card rounded-[18px] p-4 shadow-sm">
                <h2 className="mb-3 text-[17px] font-extrabold">
                    {t('stats.losing')} <span className="text-muted-foreground text-sm font-medium">{t('stats.losing_window')}</span>
                </h2>
                <div className="grid gap-2.5 md:grid-cols-2">
                    {lostCustomers.map((customer) => {
                        const waText = `مرحبا ${customer.ownerAr}، صار وقتها نطمّن على ${customer.carAr} 🚗 بنستناك في ${shop.nameAr}`;

                        return (
                            <div
                                key={`${customer.owner}-${customer.car}`}
                                className="border-border flex items-center justify-between gap-2 rounded-xl border p-3"
                            >
                                <div>
                                    <div className="text-base font-bold">
                                        {customer.owner} · {customer.car}
                                    </div>
                                    <div className="text-muted-foreground text-sm">{customer.lastVisit}</div>
                                </div>
                                <a
                                    href={`https://wa.me/${customer.whatsapp}?text=${encodeURIComponent(waText)}`}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="bg-success text-success-foreground flex h-12 items-center gap-1.5 rounded-xl px-4 text-[15px] font-bold whitespace-nowrap"
                                >
                                    <MessageCircle className="size-4" aria-hidden />
                                    {t('common.whatsapp')}
                                </a>
                            </div>
                        );
                    })}
                    {lostCustomers.length === 0 && <div className="text-muted-foreground text-base">{t('stats.no_losing')}</div>}
                </div>
            </div>
        </ShopLayout>
    );
}
