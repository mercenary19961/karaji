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

            <div className="bg-card rounded-[18px] p-4 shadow-sm">
                <div className="flex items-center justify-between">
                    <h1 className="text-[21px] font-extrabold">{car.label}</h1>
                    <span className="bg-secondary text-secondary-foreground rounded-lg px-3 py-1 text-[15px] font-extrabold tracking-wider">
                        {car.plate}
                    </span>
                </div>
                <div className="text-muted-foreground mt-1.5 text-[17px]">
                    {car.owner} — {car.phone}
                </div>
                <div className="text-muted-foreground mt-1 text-[15px]">شهر الترخيص: {car.licenseMonth}</div>
                <div className="mt-3.5 flex gap-2.5">
                    <a
                        href={`tel:${car.phone}`}
                        className="bg-primary text-primary-foreground flex h-13 flex-1 items-center justify-center gap-2 rounded-xl text-[17px] font-bold"
                    >
                        <Phone className="size-5" aria-hidden />
                        اتصال
                    </a>
                    <a
                        href={waHref}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="bg-success text-success-foreground flex h-13 flex-1 items-center justify-center gap-2 rounded-xl text-[17px] font-bold"
                    >
                        <MessageCircle className="size-5" aria-hidden />
                        واتساب
                    </a>
                </div>
            </div>

            <div className="border-cta bg-due flex items-center gap-3 rounded-2xl border-2 p-4">
                <Bell className="text-due-foreground size-6 shrink-0" aria-hidden />
                <div>
                    <div className="text-due-foreground text-base font-extrabold">الزيت القادم</div>
                    <div className="text-due-foreground/85 mt-0.5 text-base">
                        عند {car.nextDue.km} كم أو {car.nextDue.date}
                    </div>
                </div>
            </div>

            <h2 className="mt-1 text-lg font-extrabold">سجل الزيارات</h2>
            <div className="flex flex-col">
                {car.visits.map((visit) => (
                    <div key={visit.date} className="flex gap-3.5">
                        <div className="flex flex-col items-center">
                            <div className="bg-primary mt-1.5 size-3.5 rounded-full" />
                            <div className="bg-input w-0.5 flex-1" />
                        </div>
                        <div className="bg-card mb-3.5 flex-1 rounded-2xl p-4 shadow-sm">
                            <div className="flex items-center justify-between">
                                <div className="text-base font-extrabold">{visit.date}</div>
                                <div className="text-success text-[15px] font-bold">{visit.price}</div>
                            </div>
                            <div className="text-muted-foreground mt-0.5 text-[15px]">العداد: {visit.km} كم</div>
                            <div className="mt-2 flex flex-wrap gap-1.5">
                                {visit.services.map((service) => (
                                    <span key={service} className="bg-secondary text-secondary-foreground rounded-full px-3 py-1 text-sm font-bold">
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
