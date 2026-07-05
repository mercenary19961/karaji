import ShopLayout from '@/layouts/shop-layout';
import { type Car, type Shop } from '@/types/shop';
import { Head } from '@inertiajs/react';
import { Bell, MessageCircle, Phone } from 'lucide-react';

interface Props {
    shop: Shop;
    car: Car;
}

export default function CarProfile({ shop, car }: Props) {
    const greeting = `مرحباً ${car.owner}، معك ${shop.name} بخصوص ${car.label} 🚗`;
    const waHref = `https://wa.me/${car.whatsapp}?text=${encodeURIComponent(greeting)}`;

    return (
        <ShopLayout shop={shop}>
            <Head title="ملف السيارة" />

            <div className="rounded-[18px] bg-card p-4 shadow-sm">
                <div className="flex items-center justify-between">
                    <h1 className="text-[21px] font-extrabold">{car.label}</h1>
                    <span className="rounded-lg bg-secondary px-3 py-1 text-[15px] font-extrabold tracking-wider text-secondary-foreground">
                        {car.plate}
                    </span>
                </div>
                <div className="mt-1.5 text-[17px] text-muted-foreground">
                    {car.owner} — {car.phone}
                </div>
                <div className="mt-1 text-[15px] text-muted-foreground">شهر الترخيص: {car.licenseMonth}</div>
                <div className="mt-3.5 flex gap-2.5">
                    <a
                        href={`tel:${car.phone}`}
                        className="flex h-13 flex-1 items-center justify-center gap-2 rounded-xl bg-primary text-[17px] font-bold text-primary-foreground"
                    >
                        <Phone className="size-5" aria-hidden />
                        اتصال
                    </a>
                    <a
                        href={waHref}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="flex h-13 flex-1 items-center justify-center gap-2 rounded-xl bg-success text-[17px] font-bold text-success-foreground"
                    >
                        <MessageCircle className="size-5" aria-hidden />
                        واتساب
                    </a>
                </div>
            </div>

            <div className="flex items-center gap-3 rounded-2xl border-2 border-cta bg-due p-4">
                <Bell className="size-6 shrink-0 text-due-foreground" aria-hidden />
                <div>
                    <div className="text-base font-extrabold text-due-foreground">الزيت القادم</div>
                    <div className="mt-0.5 text-base text-due-foreground/85">
                        عند {car.nextDue.km} كم أو {car.nextDue.date}
                    </div>
                </div>
            </div>

            <h2 className="mt-1 text-lg font-extrabold">سجل الزيارات</h2>
            <div className="flex flex-col">
                {car.visits.map((visit) => (
                    <div key={visit.date} className="flex gap-3.5">
                        <div className="flex flex-col items-center">
                            <div className="mt-1.5 size-3.5 rounded-full bg-primary" />
                            <div className="w-0.5 flex-1 bg-input" />
                        </div>
                        <div className="mb-3.5 flex-1 rounded-2xl bg-card p-4 shadow-sm">
                            <div className="flex items-center justify-between">
                                <div className="text-base font-extrabold">{visit.date}</div>
                                <div className="text-[15px] font-bold text-success">{visit.price}</div>
                            </div>
                            <div className="mt-0.5 text-[15px] text-muted-foreground">العداد: {visit.km} كم</div>
                            <div className="mt-2 flex flex-wrap gap-1.5">
                                {visit.services.map((service) => (
                                    <span
                                        key={service}
                                        className="rounded-full bg-secondary px-3 py-1 text-sm font-bold text-secondary-foreground"
                                    >
                                        {service}
                                    </span>
                                ))}
                            </div>
                        </div>
                    </div>
                ))}
            </div>
        </ShopLayout>
    );
}
