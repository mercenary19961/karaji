import ShopLayout from '@/layouts/shop-layout';
import { searchCars } from '@/lib/car-search';
import { useT } from '@/lib/i18n';
import { type CarSearchItem, type Shop } from '@/types/shop';
import { Head, router } from '@inertiajs/react';
import { Search, Users } from 'lucide-react';
import { useMemo, useState } from 'react';

interface Props {
    shop: Shop;
    clients: CarSearchItem[];
}

export default function Clients({ shop, clients }: Props) {
    const t = useT();
    const [q, setQ] = useState('');

    const query = q.trim();
    // Empty query → the whole directory (recency-sorted); else ranked matches
    const showing = useMemo(() => (query ? searchCars(clients, q) : clients), [clients, q, query]);

    const openCar = (id: number) => router.get(route('shop.cars.show', id));

    return (
        <ShopLayout shop={shop}>
            <Head title={t('clients.title')} />

            <div className="mx-auto flex w-full flex-col gap-4 md:max-w-2xl">
                <div className="flex items-baseline justify-between gap-2">
                    <h1 className="text-xl font-extrabold">{t('clients.title')}</h1>
                    <span className="text-muted-foreground text-[15px] font-bold">{t('clients.count', { count: clients.length })}</span>
                </div>

                <div className="relative">
                    <Search className="text-muted-foreground pointer-events-none absolute start-4 top-1/2 size-5 -translate-y-1/2" aria-hidden />
                    <input
                        type="text"
                        enterKeyHint="search"
                        placeholder={t('visit.search')}
                        value={q}
                        onChange={(e) => setQ(e.target.value)}
                        className="border-input bg-card focus-visible:border-ring h-15 w-full rounded-2xl border-2 ps-12 pe-4 text-[18px] outline-none"
                    />
                </div>

                <div className="flex flex-col gap-2.5">
                    {showing.map((car) => (
                        <button
                            key={car.id}
                            type="button"
                            onClick={() => openCar(car.id)}
                            className="bg-card hover:border-primary flex items-center justify-between gap-3 rounded-2xl border-2 border-transparent p-4 text-start shadow-sm transition-colors"
                        >
                            <div className="min-w-0">
                                <div className="truncate text-[17px] font-bold">
                                    {car.label} · {car.owner}
                                </div>
                                <div className="text-muted-foreground mt-0.5 text-[15px]">
                                    {car.lastVisit ? t('entry.last_visit', { date: car.lastVisit }) : t('entry.no_last_visit')} · {car.phone}
                                </div>
                            </div>
                            <span className="bg-secondary text-secondary-foreground shrink-0 rounded-lg px-3 py-1 text-[15px] font-extrabold tracking-wider">
                                {car.plate}
                            </span>
                        </button>
                    ))}

                    {showing.length === 0 && (
                        <div className="bg-card text-muted-foreground flex flex-col items-center gap-2 rounded-2xl p-6 text-center text-base">
                            <Users className="size-7" aria-hidden />
                            {query ? t('entry.no_results') : t('clients.empty')}
                        </div>
                    )}
                </div>
            </div>
        </ShopLayout>
    );
}
