import ShopLayout from '@/layouts/shop-layout';
import { type Analytics, type Shop } from '@/types/shop';
import { Head } from '@inertiajs/react';
import { ChevronLeft, ChevronRight, MessageCircle } from 'lucide-react';
import { useState } from 'react';

interface Props {
    shop: Shop;
    analytics: Analytics;
}

const CHART = { width: 320, height: 150, barWidth: 34, gap: 50, baseline: 140, maxBarHeight: 115 };

export default function AnalyticsPage({ shop, analytics }: Props) {
    const { months, topServices, lostCustomers } = analytics;
    const [monthIdx, setMonthIdx] = useState(months.length - 1);

    const step = (dir: number) => setMonthIdx((i) => (i + dir + months.length) % months.length);

    const maxVisits = Math.max(...months.map((m) => m.visits));

    return (
        <ShopLayout shop={shop}>
            <Head title="التقارير" />

            <div className="bg-card flex items-center justify-between rounded-2xl p-1.5 shadow-sm">
                <button
                    type="button"
                    onClick={() => step(-1)}
                    aria-label="الشهر السابق"
                    className="bg-secondary text-primary flex size-13 items-center justify-center rounded-xl"
                >
                    <ChevronRight className="size-6" aria-hidden />
                </button>
                <div className="text-lg font-extrabold">{months[monthIdx].label} 2026</div>
                <button
                    type="button"
                    onClick={() => step(1)}
                    aria-label="الشهر التالي"
                    className="bg-secondary text-primary flex size-13 items-center justify-center rounded-xl"
                >
                    <ChevronLeft className="size-6" aria-hidden />
                </button>
            </div>

            <div className="bg-card rounded-[18px] p-4 shadow-sm">
                <h2 className="mb-3 text-[17px] font-extrabold">الزيارات شهرياً</h2>
                <svg viewBox={`0 0 ${CHART.width} ${CHART.height}`} className="block h-auto w-full" role="img" aria-label="عدد الزيارات شهرياً">
                    {months.map((month, i) => {
                        const barHeight = Math.round((month.visits / maxVisits) * CHART.maxBarHeight);
                        const x = 18 + i * CHART.gap;
                        const y = CHART.baseline - barHeight;
                        const active = i === monthIdx;

                        return (
                            <g key={month.label}>
                                <rect
                                    x={x}
                                    y={y}
                                    width={CHART.barWidth}
                                    height={barHeight}
                                    rx={6}
                                    fill={active ? 'var(--primary)' : 'var(--input)'}
                                />
                                {active && (
                                    <text
                                        x={x + CHART.barWidth / 2}
                                        y={y - 7}
                                        fontSize={13}
                                        fontWeight={800}
                                        fill="var(--primary)"
                                        textAnchor="middle"
                                    >
                                        {month.visits}
                                    </text>
                                )}
                                <text x={x + CHART.barWidth / 2} y={148} fontSize={11} fill="var(--muted-foreground)" textAnchor="middle">
                                    {month.label}
                                </text>
                            </g>
                        );
                    })}
                </svg>
            </div>

            <div className="bg-card rounded-[18px] p-4 shadow-sm">
                <h2 className="mb-3 text-[17px] font-extrabold">أكثر الخدمات</h2>
                <div className="flex flex-col gap-2.5">
                    {topServices.map((service) => (
                        <div key={service.label} className="flex justify-between text-base">
                            <span>{service.label}</span>
                            <b className="text-primary">{service.count}</b>
                        </div>
                    ))}
                </div>
            </div>

            <div className="bg-card rounded-[18px] p-4 shadow-sm">
                <h2 className="mb-3 text-[17px] font-extrabold">
                    زبائن ما رجعوا <span className="text-muted-foreground text-sm font-medium">(أكثر من 6 أشهر)</span>
                </h2>
                <div className="flex flex-col gap-2.5">
                    {lostCustomers.map((customer) => {
                        const waText = `مرحباً ${customer.owner}، صار وقت نطمّن على ${customer.car} 🚗 بانتظارك في ${shop.name}`;

                        return (
                            <div key={customer.owner} className="border-border flex items-center justify-between gap-2 rounded-xl border p-3">
                                <div>
                                    <div className="text-base font-bold">
                                        {customer.owner} — {customer.car}
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
                                    واتساب
                                </a>
                            </div>
                        );
                    })}
                </div>
            </div>
        </ShopLayout>
    );
}
