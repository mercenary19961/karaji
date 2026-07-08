import ShopLayout from '@/layouts/shop-layout';
import { useT } from '@/lib/i18n';
import { type CarSearchItem, type Shop } from '@/types/shop';
import { Head, Link, router } from '@inertiajs/react';
import { Clock, Search, UserPlus } from 'lucide-react';
import { useMemo, useRef, useState } from 'react';

interface Props {
    shop: Shop;
    cars: CarSearchItem[];
}

/**
 * Ranks a car against the query. Numbers match plate/phone (a leading match
 * beats a contains match); text matches owner/label. Returns 0 for no match.
 */
function scoreCar(car: CarSearchItem, query: string, digits: string): number {
    const plate = car.plate.toLowerCase();
    const plateDigits = plate.replace(/\D/g, '');
    const phone = car.phone.replace(/\D/g, '');
    const owner = car.owner.toLowerCase();
    const label = car.label.toLowerCase();

    let score = 0;
    if (digits) {
        if (plateDigits.startsWith(digits)) score = Math.max(score, 100);
        else if (plateDigits.includes(digits)) score = Math.max(score, 72);
        if (phone.startsWith(digits)) score = Math.max(score, 92);
        else if (phone.includes(digits)) score = Math.max(score, 62);
    }
    if (owner.includes(query)) score = Math.max(score, owner.startsWith(query) ? 88 : 56);
    if (label.includes(query)) score = Math.max(score, label.startsWith(query) ? 82 : 52);
    if (plate.includes(query)) score = Math.max(score, 66); // plate typed with its dash
    return score;
}

export default function Entry({ shop, cars }: Props) {
    const t = useT();
    const [q, setQ] = useState('');
    const inputRef = useRef<HTMLInputElement>(null);

    const query = q.trim().toLowerCase();
    const digits = query.replace(/\D/g, '');

    // Instant, client-side — no per-keystroke round-trip.
    const results = useMemo(() => {
        if (!query) return [];
        return cars
            .map((car) => ({ car, score: scoreCar(car, query, digits) }))
            .filter((r) => r.score > 0)
            .sort((a, b) => b.score - a.score)
            .slice(0, 8)
            .map((r) => r.car);
    }, [cars, query, digits]);

    // `cars` arrives newest-visit first, so the head is the recent list.
    const recent = useMemo(() => cars.slice(0, 6), [cars]);

    const openCar = (id: number) => router.get(route('shop.visits.create', { car: id }));
    const newCustomerHref = route('shop.visits.create', { new: 1 });

    const showing = query ? results : recent;
    const heading = query ? t('entry.results') : t('entry.recent');

    return (
        <ShopLayout shop={shop}>
            <Head title={t('nav.new_visit')} />

            <div className="mx-auto flex w-full flex-col gap-4 md:max-w-2xl">
                <div className="flex items-center justify-between gap-3">
                    <h1 className="text-xl font-extrabold">{t('nav.new_visit')}</h1>
                    {/* Desktop new-customer action (mobile gets the floating button) */}
                    <Link
                        href={newCustomerHref}
                        className="bg-cta text-cta-foreground hidden h-11 items-center gap-2 rounded-xl px-4 text-[15px] font-extrabold md:inline-flex"
                    >
                        <UserPlus className="size-5" aria-hidden />
                        {t('entry.new_customer')}
                    </Link>
                </div>

                {/* Instant search */}
                <div className="relative">
                    <Search className="text-muted-foreground pointer-events-none absolute start-4 top-1/2 size-5 -translate-y-1/2" aria-hidden />
                    <input
                        ref={inputRef}
                        autoFocus
                        type="text"
                        enterKeyHint="search"
                        placeholder={t('visit.search')}
                        value={q}
                        onChange={(e) => setQ(e.target.value)}
                        className="border-input bg-card focus-visible:border-ring h-16 w-full rounded-2xl border-2 ps-12 pe-4 text-[18px] outline-none"
                    />
                </div>

                {/* Results / recents */}
                <div className="flex flex-col gap-2.5">
                    {showing.length > 0 && <div className="text-muted-foreground px-1 text-[15px] font-bold">{heading}</div>}

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

                    {/* No search match */}
                    {query && results.length === 0 && (
                        <div className="bg-card flex flex-col items-center gap-2 rounded-2xl p-6 text-center">
                            <Search className="text-muted-foreground size-7" aria-hidden />
                            <div className="text-base font-bold">{t('entry.no_results')}</div>
                            <div className="text-muted-foreground text-[15px]">{t('entry.no_results_hint')}</div>
                            <Link
                                href={newCustomerHref}
                                className="bg-cta text-cta-foreground mt-1 flex h-12 items-center gap-2 rounded-xl px-5 text-[16px] font-extrabold"
                            >
                                <UserPlus className="size-5" aria-hidden />
                                {t('entry.new_customer')}
                            </Link>
                        </div>
                    )}

                    {/* No customers at all yet */}
                    {!query && recent.length === 0 && (
                        <div className="bg-card text-muted-foreground flex flex-col items-center gap-2 rounded-2xl p-6 text-center text-base">
                            <Clock className="size-7" aria-hidden />
                            {t('entry.no_recent')}
                        </div>
                    )}
                </div>
            </div>

            {/* Mobile floating action: new car / customer (above the bottom nav) */}
            <Link
                href={newCustomerHref}
                className="bg-cta text-cta-foreground shadow-cta/40 fixed bottom-24 left-1/2 z-20 flex h-14 -translate-x-1/2 items-center gap-2 rounded-full px-6 text-[17px] font-extrabold shadow-xl md:hidden"
            >
                <UserPlus className="size-5" aria-hidden />
                {t('entry.new_customer')}
            </Link>
        </ShopLayout>
    );
}
