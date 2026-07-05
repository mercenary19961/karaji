import ShopLayout from '@/layouts/shop-layout';
import { type Reminder, type Shop } from '@/types/shop';
import { Head } from '@inertiajs/react';
import { Check, MessageCircle, Phone } from 'lucide-react';
import { useState } from 'react';

interface Props {
    shop: Shop;
    reminders: Reminder[];
}

export default function Reminders({ shop, reminders }: Props) {
    // Demo: contacted state is client-side only until reminders exist in the schema
    const [contacted, setContacted] = useState<Record<string, boolean>>({});

    const toggle = (id: string) => setContacted((c) => ({ ...c, [id]: !c[id] }));

    return (
        <ShopLayout shop={shop}>
            <Head title="التذكيرات" />

            <div className="flex items-baseline justify-between">
                <h1 className="text-xl font-extrabold">قائمة التذكير</h1>
                <div className="text-muted-foreground text-[15px]">مرتبة حسب الأكثر تأخراً</div>
            </div>

            {reminders.map((reminder) => {
                const done = !!contacted[reminder.id];
                const waText = `مرحباً ${reminder.owner}، سيارتك ${reminder.car} مستحقة: ${reminder.due}. بانتظارك في ${shop.name} 🔧`;

                return (
                    <div
                        key={reminder.id}
                        className={`rounded-[18px] p-4 ${
                            done ? 'border-success bg-success-soft border-2 opacity-85' : 'bg-card border-2 border-transparent shadow-sm'
                        }`}
                    >
                        <div className="flex items-start justify-between gap-2">
                            <div>
                                <div className="text-lg font-extrabold">{reminder.car}</div>
                                <div className="text-muted-foreground mt-0.5 text-base">
                                    {reminder.owner} — {reminder.phone}
                                </div>
                                <div className="text-primary mt-1.5 text-base font-bold">{reminder.due}</div>
                            </div>
                            <span
                                className={`rounded-full px-3 py-1.5 text-sm font-bold whitespace-nowrap ${
                                    done ? 'bg-success text-success-foreground' : 'bg-due text-due-foreground'
                                }`}
                            >
                                {reminder.overdueLabel}
                            </span>
                        </div>

                        {!done && (
                            <div className="mt-3.5 flex gap-2.5">
                                <a
                                    href={`tel:${reminder.phone}`}
                                    className="bg-primary text-primary-foreground flex h-13 flex-1 items-center justify-center gap-2 rounded-xl text-[17px] font-bold"
                                >
                                    <Phone className="size-5" aria-hidden />
                                    اتصال
                                </a>
                                <a
                                    href={`https://wa.me/${reminder.whatsapp}?text=${encodeURIComponent(waText)}`}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="bg-success text-success-foreground flex h-13 flex-1 items-center justify-center gap-2 rounded-xl text-[17px] font-bold"
                                >
                                    <MessageCircle className="size-5" aria-hidden />
                                    واتساب
                                </a>
                            </div>
                        )}

                        <button
                            type="button"
                            onClick={() => toggle(reminder.id)}
                            className={`mt-2.5 flex h-[50px] w-full items-center justify-center gap-1.5 rounded-xl text-base font-extrabold ${
                                done ? 'bg-success text-success-foreground' : 'border-success bg-card text-success-soft-foreground border-2'
                            }`}
                        >
                            <Check className="size-5" aria-hidden />
                            {done ? 'تم التواصل — إلغاء' : 'تم التواصل'}
                        </button>
                    </div>
                );
            })}
        </ShopLayout>
    );
}
