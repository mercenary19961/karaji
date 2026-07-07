import ShopLayout from '@/layouts/shop-layout';
import { useT } from '@/lib/i18n';
import { type CarProfile, type Shop } from '@/types/shop';
import { Head, Link } from '@inertiajs/react';
import { Bell, MessageCircle, Phone, Plus } from 'lucide-react';

interface Props {
    shop: Shop;
    car: CarProfile;
}

export default function CarProfilePage({ shop, car }: Props) {
    const t = useT();
    // Customer-facing message stays Arabic regardless of UI language
    const greeting = `مرحبا ${car.owner}، معك ${shop.nameAr} بخصوص ${car.label} 🚗`;
    const waHref = `https://wa.me/${car.whatsapp}?text=${encodeURIComponent(greeting)}`;

    return (
        <ShopLayout shop={shop}>
            <Head title={car.label} />

            <div className="grid gap-4 md:grid-cols-[1fr_1.3fr] md:items-start md:gap-6">
                <div className="flex flex-col gap-4">
                    <div className="bg-card rounded-[18px] p-4 shadow-sm">
                        <div className="flex items-center justify-between">
                            <h1 className="text-[21px] font-extrabold">{car.label}</h1>
                            <span className="bg-secondary text-secondary-foreground rounded-lg px-3 py-1 text-[15px] font-extrabold tracking-wider">
                                {car.plate}
                            </span>
                        </div>
                        <div className="text-muted-foreground mt-1.5 text-[17px]">
                            {car.owner} · {car.phone}
                        </div>
                        {car.licenseMonth && (
                            <div className="text-muted-foreground mt-1 text-[15px]">{t('car.license_month', { month: car.licenseMonth })}</div>
                        )}
                        {car.lastService && <div className="text-muted-foreground mt-1 text-[15px]">{car.lastService}</div>}
                        <div className="mt-3.5 flex gap-2.5">
                            <a
                                href={`tel:${car.phone}`}
                                className="bg-primary text-primary-foreground flex h-13 flex-1 items-center justify-center gap-2 rounded-xl text-[17px] font-bold"
                            >
                                <Phone className="size-5" aria-hidden />
                                {t('common.call')}
                            </a>
                            <a
                                href={waHref}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="bg-success text-success-foreground flex h-13 flex-1 items-center justify-center gap-2 rounded-xl text-[17px] font-bold"
                            >
                                <MessageCircle className="size-5" aria-hidden />
                                {t('common.whatsapp')}
                            </a>
                        </div>
                    </div>

                    <Link
                        href={route('shop.visits.create', { car: car.id })}
                        className="bg-cta text-cta-foreground shadow-cta/35 flex h-15 items-center justify-center gap-2 rounded-2xl text-[20px] font-extrabold shadow-lg"
                    >
                        <Plus className="size-6" aria-hidden />
                        {t('car.new_visit')}
                    </Link>

                    {car.nextDue && (
                        <div className="border-cta bg-due flex items-center gap-3 rounded-2xl border-2 p-4">
                            <Bell className="text-due-foreground size-6 shrink-0" aria-hidden />
                            <div>
                                <div className="text-due-foreground text-base font-extrabold">{t('car.next_oil')}</div>
                                <div className="text-due-foreground/85 mt-0.5 text-base">
                                    {car.nextDue.km && t('car.at_km', { km: car.nextDue.km })}
                                    {car.nextDue.km && car.nextDue.date && ` ${t('car.or')} `}
                                    {car.nextDue.date}
                                </div>
                            </div>
                        </div>
                    )}
                </div>

                <div className="flex flex-col gap-3">
                    <h2 className="text-lg font-extrabold">{t('car.visits_log')}</h2>
                    <div className="flex flex-col">
                        {car.visits.map((visit, i) => (
                            <div key={`${visit.date}-${i}`} className="flex gap-3.5">
                                <div className="flex flex-col items-center">
                                    <div className="bg-primary mt-1.5 size-3.5 rounded-full" />
                                    <div className="bg-input w-0.5 flex-1" />
                                </div>
                                <div className="bg-card mb-3.5 flex-1 rounded-2xl p-4 shadow-sm">
                                    <div className="flex items-center justify-between">
                                        <div className="text-base font-extrabold">{visit.date}</div>
                                        {visit.price && <div className="text-success text-[15px] font-bold">{visit.price}</div>}
                                    </div>
                                    <div className="text-muted-foreground mt-0.5 text-[15px]">{t('car.odometer', { km: visit.km })}</div>
                                    <div className="mt-2 flex flex-wrap gap-1.5">
                                        {visit.services.map((service) => (
                                            <span
                                                key={service}
                                                className="bg-secondary text-secondary-foreground rounded-full px-3 py-1 text-sm font-bold"
                                            >
                                                {service}
                                            </span>
                                        ))}
                                    </div>
                                </div>
                            </div>
                        ))}
                        {car.visits.length === 0 && (
                            <div className="bg-card text-muted-foreground rounded-2xl p-5 text-center text-base">{t('car.no_visits')}</div>
                        )}
                    </div>
                </div>
            </div>
        </ShopLayout>
    );
}
