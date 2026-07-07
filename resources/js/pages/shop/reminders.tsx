import ShopLayout from '@/layouts/shop-layout';
import { useT } from '@/lib/i18n';
import { type Reminder, type Shop } from '@/types/shop';
import { Head, router } from '@inertiajs/react';
import { Check, MessageCircle, Phone } from 'lucide-react';

interface Props {
    shop: Shop;
    reminders: Reminder[];
}

export default function Reminders({ shop, reminders }: Props) {
    const t = useT();
    const toggle = (id: number) => router.post(route('shop.reminders.contact', id), {}, { preserveScroll: true });

    return (
        <ShopLayout shop={shop}>
            <Head title={t('rem.title')} />

            <div className="flex items-baseline justify-between">
                <h1 className="text-xl font-extrabold">{t('rem.title')}</h1>
                <div className="text-muted-foreground text-[15px]">{t('rem.subtitle')}</div>
            </div>

            <div className="grid gap-3.5 md:grid-cols-2 md:items-start xl:grid-cols-3">
                {reminders.map((reminder) => {
                    const waText = `مرحبا ${reminder.owner}، سيارتك ${reminder.car} صار إلها ${reminder.due}. بنستناك في ${shop.name} 🔧`;

                    return (
                        <div
                            key={reminder.id}
                            className={`rounded-[18px] p-4 ${
                                reminder.contacted
                                    ? 'border-success bg-success-soft border-2 opacity-85'
                                    : 'bg-card border-2 border-transparent shadow-sm'
                            }`}
                        >
                            <div className="flex items-start justify-between gap-2">
                                <div>
                                    <div className="text-lg font-extrabold">{reminder.car}</div>
                                    <div className="text-muted-foreground mt-0.5 text-base">
                                        {reminder.owner} · {reminder.phone}
                                    </div>
                                    <div className="text-primary mt-1.5 text-base font-bold">{reminder.due}</div>
                                </div>
                                <span
                                    className={`rounded-full px-3 py-1.5 text-sm font-bold whitespace-nowrap ${
                                        reminder.contacted ? 'bg-success text-success-foreground' : 'bg-due text-due-foreground'
                                    }`}
                                >
                                    {reminder.overdueLabel}
                                </span>
                            </div>

                            {!reminder.contacted && (
                                <div className="mt-3.5 flex gap-2.5">
                                    <a
                                        href={`tel:${reminder.phone}`}
                                        className="bg-primary text-primary-foreground flex h-13 flex-1 items-center justify-center gap-2 rounded-xl text-[17px] font-bold"
                                    >
                                        <Phone className="size-5" aria-hidden />
                                        {t('common.call')}
                                    </a>
                                    <a
                                        href={`https://wa.me/${reminder.whatsapp}?text=${encodeURIComponent(waText)}`}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="bg-success text-success-foreground flex h-13 flex-1 items-center justify-center gap-2 rounded-xl text-[17px] font-bold"
                                    >
                                        <MessageCircle className="size-5" aria-hidden />
                                        {t('common.whatsapp')}
                                    </a>
                                </div>
                            )}

                            <button
                                type="button"
                                onClick={() => toggle(reminder.id)}
                                className={`mt-2.5 flex h-[50px] w-full items-center justify-center gap-1.5 rounded-xl text-base font-extrabold ${
                                    reminder.contacted
                                        ? 'bg-success text-success-foreground'
                                        : 'border-success bg-card text-success-soft-foreground border-2'
                                }`}
                            >
                                <Check className="size-5" aria-hidden />
                                {reminder.contacted ? t('rem.contacted_undo') : t('rem.contacted')}
                            </button>
                        </div>
                    );
                })}
            </div>

            {reminders.length === 0 && <div className="bg-card text-muted-foreground rounded-2xl p-6 text-center text-base">{t('rem.none')}</div>}
        </ShopLayout>
    );
}
