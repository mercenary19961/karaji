import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import ShopLayout from '@/layouts/shop-layout';
import { useT } from '@/lib/i18n';
import { type SharedData } from '@/types';
import { type FormCar, type OilTypeOption, type SavedVisit, type ServiceTypeOption, type Shop } from '@/types/shop';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { Check, MessageCircle, Pencil, Search } from 'lucide-react';
import { type FormEvent, useState } from 'react';

interface Props {
    shop: Shop;
    car: FormCar | null;
    startNew: boolean;
    serviceTypes: ServiceTypeOption[];
    oilBrands: string[];
    oilTypes: OilTypeOption[];
    savedVisit: SavedVisit | null;
}

const inputClasses = 'border-input bg-card focus-visible:border-ring h-14 w-full rounded-xl border-2 px-4 text-lg outline-none';

function FieldError({ message }: { message?: string }) {
    if (!message) return null;

    return <div className="text-destructive mt-1.5 text-[15px] font-bold">{message}</div>;
}

export default function NewVisit({ shop, car, startNew, serviceTypes, oilBrands, oilTypes, savedVisit }: Props) {
    const { flash } = usePage<SharedData>().props;
    const t = useT();

    // Compares service NAMES (Arabic data), unaffected by UI language
    const oilChangeId = serviceTypes.find((s) => s.name === 'تغيير زيت')?.id;
    const defaultServices = serviceTypes.filter((s) => s.name === 'تغيير زيت' || s.name === 'فلتر زيت').map((s) => s.id);
    const [newCust, setNewCust] = useState(startNew);
    const [q, setQ] = useState('');

    const defaultPriceFor = (id: number) => serviceTypes.find((s) => s.id === id)?.defaultPrice ?? '';

    const form = useForm<{
        name: string;
        phone: string;
        plate: string;
        label: string;
        km: string;
        services: number[];
        prices: Record<number, string>;
        labor: string;
        oil_brand: string;
        oil_type: string;
    }>({
        name: '',
        phone: '',
        plate: '',
        label: '',
        km: '',
        services: defaultServices,
        prices: Object.fromEntries(defaultServices.map((id) => [id, defaultPriceFor(id)])),
        labor: '',
        oil_brand: car?.lastOilBrand ?? oilBrands[0],
        oil_type: car?.lastOilType ?? oilTypes[0]?.key ?? '',
    });

    // The oil-type control only matters when this visit changes the oil
    const oilChangeSelected = oilChangeId !== undefined && form.data.services.includes(oilChangeId);

    // Per-service price rows + labor → a live total (the visit's revenue)
    const selectedServices = serviceTypes.filter((s) => form.data.services.includes(s.id));
    const partsSum = selectedServices.reduce((sum, s) => sum + (parseFloat(form.data.prices[s.id] ?? '') || 0), 0);
    const total = Math.round((partsSum + (parseFloat(form.data.labor || '') || 0)) * 100) / 100;

    const searchCar = (e: FormEvent) => {
        e.preventDefault();
        router.get(route('shop.cars.search'), { q, to: 'visit' });
    };

    const toggleService = (id: number) => {
        const on = form.data.services.includes(id);
        const services = on ? form.data.services.filter((s) => s !== id) : [...form.data.services, id];
        const prices = { ...form.data.prices };
        if (on) {
            delete prices[id];
        } else if (prices[id] === undefined) {
            prices[id] = defaultPriceFor(id); // pre-fill from the shop's saved default
        }
        form.setData({ ...form.data, services, prices });
    };

    const save = (e: FormEvent) => {
        e.preventDefault();
        form.transform((data) => ({
            ...data,
            car_id: newCust ? null : (car?.id ?? null),
            km: data.km === '' ? null : Number(data.km.replace(/\D/g, '')),
            labor: data.labor === '' ? null : data.labor,
            // Only the selected services' prices; blanks go as null (no price)
            prices: Object.fromEntries(
                Object.entries(data.prices)
                    .filter(([id]) => data.services.includes(Number(id)))
                    .map(([id, price]) => [id, price === '' ? null : price]),
            ),
        }));
        form.post(route('shop.visits.store'), { preserveScroll: true });
    };

    // ===== Post-save success state (the digital windshield sticker) =====
    if (savedVisit) {
        const summary = [
            `مرحبا ${savedVisit.ownerAr}،`,
            `عملنا اليوم صيانة ${savedVisit.carLabelAr} (لوحة ${savedVisit.plate}):`,
            ...savedVisit.services.map((s) => `✅ ${s}${s === 'تغيير زيت' && savedVisit.oilBrand ? ` (${savedVisit.oilBrand})` : ''}`),
            `قراءة العداد: ${savedVisit.km} كم`,
            ...(savedVisit.nextDueKm ? [`🔔 الموعد الجاي: عند ${savedVisit.nextDueKm} كم أو ${savedVisit.nextDueDate}`] : []),
            `شكراً لثقتك 🙏 ${shop.nameAr}`,
        ].join('\n');
        const waHref = `https://wa.me/${savedVisit.whatsapp}?text=${encodeURIComponent(summary)}`;

        return (
            <ShopLayout shop={shop}>
                <Head title={t('visit.saved_title')} />

                <div className="mx-auto flex w-full flex-col gap-4 md:max-w-xl">
                    <div className="border-success bg-success-soft rounded-[18px] border-2 px-4 py-6 text-center">
                        <div className="bg-success text-success-foreground mx-auto flex size-16 items-center justify-center rounded-full">
                            <Check className="size-9" aria-hidden />
                        </div>
                        <div className="text-success-soft-foreground mt-3 text-[21px] font-extrabold">{t('visit.saved_title')}</div>
                        <div className="text-success-soft-foreground/80 mt-1 text-[15px]">
                            {savedVisit.carLabel} · {savedVisit.owner} · {t('visit.saved_meter', { km: savedVisit.km })}
                        </div>
                    </div>

                    <a
                        href={waHref}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="bg-success text-success-foreground shadow-success/35 flex h-17 items-center justify-center gap-2 rounded-2xl text-[21px] font-extrabold shadow-lg"
                    >
                        <MessageCircle className="size-6" aria-hidden />
                        {t('visit.send_summary')}
                    </a>

                    <div className="text-muted-foreground text-center text-sm">{t('visit.preview')}</div>
                    <div className="ms-8 rounded-[18px] rounded-es-sm bg-[#dcf3d0] p-4 text-[15.5px] leading-8 whitespace-pre-line text-[#1e3325] shadow-sm">
                        {summary}
                    </div>

                    <Link
                        href={route('shop.dashboard')}
                        className="border-input bg-card text-primary flex h-14 items-center justify-center rounded-2xl border-2 text-lg font-bold"
                    >
                        {t('visit.back_home')}
                    </Link>

                    <button
                        type="button"
                        onClick={() => router.delete(route('shop.visits.destroy', savedVisit.id))}
                        className="text-destructive min-h-12 text-center text-[15px] font-bold underline"
                    >
                        {t('visit.undo')}
                    </button>
                </div>
            </ShopLayout>
        );
    }

    // ===== Entry form =====
    return (
        <ShopLayout shop={shop}>
            <Head title={t('visit.title')} />

            <div className="mx-auto flex w-full flex-col gap-4 md:max-w-xl">
                <h1 className="text-xl font-extrabold">{t('visit.title')}</h1>

                {!newCust && car && (
                    <div className="border-primary bg-card rounded-2xl border-2 p-4 shadow-sm">
                        <div className="flex items-center justify-between">
                            <div className="text-[19px] font-extrabold">{car.label}</div>
                            <span className="bg-secondary text-secondary-foreground rounded-lg px-3 py-1 text-[15px] font-extrabold tracking-wider">
                                {car.plate}
                            </span>
                        </div>
                        <div className="text-muted-foreground mt-1.5 text-base">
                            {car.owner} · {car.phone}
                        </div>
                        {car.lastService && <div className="text-muted-foreground mt-1 text-[15px]">{car.lastService}</div>}
                        <Link
                            href={route('shop.cars.edit', car.id)}
                            className="text-primary mt-2 flex w-fit items-center gap-1 text-[14px] font-bold"
                        >
                            <Pencil className="size-3.5" aria-hidden />
                            {t('car.edit')}
                        </Link>
                    </div>
                )}

                {!newCust && !car && (
                    <form onSubmit={searchCar}>
                        <div className="relative">
                            <Search className="text-muted-foreground absolute start-4 top-1/2 size-5 -translate-y-1/2" aria-hidden />
                            <input
                                inputMode="numeric"
                                placeholder={t('visit.search')}
                                value={q}
                                onChange={(e) => setQ(e.target.value)}
                                className="border-input bg-card focus-visible:border-ring h-15 w-full rounded-2xl border-2 ps-12 pe-4 text-[18px] outline-none"
                            />
                        </div>
                        {flash.error && <div className="text-destructive mt-2 text-[15px] font-bold">{flash.error}</div>}
                    </form>
                )}

                <button
                    type="button"
                    onClick={() => setNewCust((v) => !v)}
                    className="text-primary min-h-12 self-start px-1 text-[15px] font-bold underline"
                >
                    {newCust ? t('visit.back_registered') : t('visit.unregistered')}
                </button>

                <form onSubmit={save} className="flex flex-col gap-4">
                    {newCust && (
                        <div className="bg-secondary flex flex-col gap-3 rounded-2xl p-4">
                            <div className="text-secondary-foreground text-base font-extrabold">{t('visit.new_customer')}</div>
                            <div>
                                <input
                                    placeholder={t('visit.name')}
                                    value={form.data.name}
                                    onChange={(e) => form.setData('name', e.target.value)}
                                    className={inputClasses}
                                />
                                <FieldError message={form.errors.name} />
                            </div>
                            <div>
                                <input
                                    inputMode="tel"
                                    placeholder={t('visit.phone')}
                                    value={form.data.phone}
                                    onChange={(e) => form.setData('phone', e.target.value)}
                                    className={inputClasses}
                                />
                                <FieldError message={form.errors.phone} />
                            </div>
                            <div>
                                <input
                                    inputMode="numeric"
                                    placeholder={t('visit.plate')}
                                    value={form.data.plate}
                                    onChange={(e) => form.setData('plate', e.target.value)}
                                    className={inputClasses}
                                />
                                <FieldError message={form.errors.plate} />
                            </div>
                            <input
                                placeholder={t('visit.car_optional')}
                                value={form.data.label}
                                onChange={(e) => form.setData('label', e.target.value)}
                                className={inputClasses}
                            />
                        </div>
                    )}

                    {(car || newCust) && (
                        <>
                            <div>
                                <div className="mb-2 text-[17px] font-bold">
                                    {t('visit.km_label')} <span className="text-destructive">*</span>
                                </div>
                                <input
                                    inputMode="numeric"
                                    placeholder={t('visit.km_placeholder')}
                                    value={form.data.km}
                                    onChange={(e) => form.setData('km', e.target.value)}
                                    className="border-input bg-card focus-visible:border-ring h-16 w-full rounded-2xl border-2 px-4 text-center text-2xl font-bold tracking-wide outline-none"
                                />
                                <FieldError message={form.errors.km} />
                            </div>

                            <div>
                                <div className="mb-2 text-[17px] font-bold">{t('visit.services')}</div>
                                <div className="grid grid-cols-2 gap-2.5">
                                    {serviceTypes.map((service) => {
                                        const on = form.data.services.includes(service.id);

                                        return (
                                            <button
                                                key={service.id}
                                                type="button"
                                                onClick={() => toggleService(service.id)}
                                                className={`min-h-13 rounded-xl border-2 px-2 text-[17px] font-bold ${
                                                    on ? 'border-primary bg-primary text-primary-foreground' : 'border-input bg-card text-foreground'
                                                }`}
                                            >
                                                {on ? '✓ ' : ''}
                                                {service.label}
                                            </button>
                                        );
                                    })}
                                </div>
                                <FieldError message={form.errors.services} />
                            </div>

                            {oilChangeSelected && (
                                <>
                                    <div>
                                        <div className="mb-2 text-[17px] font-bold">{t('visit.oil_type')}</div>
                                        <div className="grid grid-cols-2 gap-2.5">
                                            {oilTypes.map((type) => {
                                                const on = form.data.oil_type === type.key;

                                                return (
                                                    <button
                                                        key={type.key}
                                                        type="button"
                                                        onClick={() => form.setData('oil_type', type.key)}
                                                        className={`min-h-13 rounded-xl border-2 px-2 text-[17px] font-bold ${
                                                            on
                                                                ? 'border-primary bg-primary text-primary-foreground'
                                                                : 'border-input bg-card text-foreground'
                                                        }`}
                                                    >
                                                        {type.label}
                                                    </button>
                                                );
                                            })}
                                        </div>
                                    </div>

                                    <div>
                                        <div className="mb-2 text-[17px] font-bold">{t('visit.oil_brand')}</div>
                                        <Select value={form.data.oil_brand} onValueChange={(v) => form.setData('oil_brand', v)}>
                                            <SelectTrigger className="h-14 text-[17px]">
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {oilBrands.map((brand) => (
                                                    <SelectItem key={brand} value={brand} className="text-[17px]">
                                                        {brand === car?.lastOilBrand ? t('visit.same_last', { brand }) : brand}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>
                                </>
                            )}

                            {selectedServices.length > 0 && (
                                <div>
                                    <div className="mb-2 text-[17px] font-bold">{t('visit.prices')}</div>
                                    <div className="divide-border/60 bg-secondary flex flex-col divide-y rounded-2xl p-2">
                                        {selectedServices.map((s) => (
                                            <div key={s.id} className="flex items-center justify-between gap-3 px-2 py-2">
                                                <span className="text-[16px] font-bold">{s.label}</span>
                                                <div className="flex items-center gap-1.5">
                                                    <input
                                                        inputMode="decimal"
                                                        value={form.data.prices[s.id] ?? ''}
                                                        onChange={(e) => form.setData('prices', { ...form.data.prices, [s.id]: e.target.value })}
                                                        placeholder="—"
                                                        aria-label={s.label}
                                                        className="border-input bg-card focus-visible:border-ring h-11 w-20 rounded-lg border-2 px-2 text-center text-[17px] font-bold outline-none"
                                                    />
                                                    <span className="text-muted-foreground text-sm font-bold">{t('common.currency')}</span>
                                                </div>
                                            </div>
                                        ))}
                                        <div className="flex items-center justify-between gap-3 px-2 py-2">
                                            <span className="text-[16px] font-bold">{t('visit.labor')}</span>
                                            <div className="flex items-center gap-1.5">
                                                <input
                                                    inputMode="decimal"
                                                    value={form.data.labor}
                                                    onChange={(e) => form.setData('labor', e.target.value)}
                                                    placeholder="—"
                                                    aria-label={t('visit.labor')}
                                                    className="border-input bg-card focus-visible:border-ring h-11 w-20 rounded-lg border-2 px-2 text-center text-[17px] font-bold outline-none"
                                                />
                                                <span className="text-muted-foreground text-sm font-bold">{t('common.currency')}</span>
                                            </div>
                                        </div>
                                        <div className="flex items-center justify-between px-2 pt-2.5 pb-1">
                                            <span className="text-[16px] font-extrabold">{t('visit.total')}</span>
                                            <span className="text-success-soft-foreground text-[18px] font-extrabold">
                                                {total} {t('common.currency')}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            )}

                            <button
                                type="submit"
                                disabled={form.processing}
                                className="bg-cta text-cta-foreground shadow-cta/35 mt-1 h-16 rounded-2xl text-[22px] font-extrabold shadow-lg disabled:opacity-60"
                            >
                                {t('visit.save')}
                            </button>
                        </>
                    )}
                </form>
            </div>
        </ShopLayout>
    );
}
