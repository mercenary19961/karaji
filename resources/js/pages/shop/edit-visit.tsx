import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import ShopLayout from '@/layouts/shop-layout';
import { useT } from '@/lib/i18n';
import { type OilTypeOption, type ServiceTypeOption, type Shop } from '@/types/shop';
import { Head, Link, useForm } from '@inertiajs/react';
import { Pencil } from 'lucide-react';
import { type FormEvent } from 'react';

interface EditableVisit {
    id: number;
    carId: number;
    carLabel: string;
    plate: string;
    owner: string;
    date: string;
    km: string;
    labor: string;
    notes: string;
    oilBrand: string | null;
    oilType: string | null;
    services: { id: number; price: string }[];
}

interface Props {
    shop: Shop;
    visit: EditableVisit;
    serviceTypes: ServiceTypeOption[];
    oilBrands: string[];
    oilTypes: OilTypeOption[];
}

const chipClasses = (on: boolean) =>
    `min-h-13 rounded-xl border-2 px-2 text-[17px] font-bold ${on ? 'border-primary bg-primary text-primary-foreground' : 'border-input bg-card text-foreground'}`;

function FieldError({ message }: { message?: string }) {
    if (!message) return null;

    return <div className="text-destructive mt-1.5 text-[15px] font-bold">{message}</div>;
}

export default function EditVisit({ shop, visit, serviceTypes, oilBrands, oilTypes }: Props) {
    const t = useT();

    // Stable Arabic key match, unaffected by UI language
    const oilChangeId = serviceTypes.find((s) => s.name === 'تغيير زيت')?.id;
    const defaultPriceFor = (id: number) => serviceTypes.find((s) => s.id === id)?.defaultPrice ?? '';

    const form = useForm<{
        km: string;
        services: number[];
        prices: Record<number, string>;
        labor: string;
        oil_brand: string;
        oil_type: string;
        notes: string;
    }>({
        km: visit.km,
        services: visit.services.map((s) => s.id),
        prices: Object.fromEntries(visit.services.map((s) => [s.id, s.price])),
        labor: visit.labor,
        oil_brand: visit.oilBrand ?? oilBrands[0],
        oil_type: visit.oilType ?? oilTypes[0]?.key ?? '',
        notes: visit.notes,
    });

    const oilChangeSelected = oilChangeId !== undefined && form.data.services.includes(oilChangeId);
    const selectedServices = serviceTypes.filter((s) => form.data.services.includes(s.id));
    const partsSum = selectedServices.reduce((sum, s) => sum + (parseFloat(form.data.prices[s.id] ?? '') || 0), 0);
    const total = Math.round((partsSum + (parseFloat(form.data.labor || '') || 0)) * 100) / 100;

    const toggleService = (id: number) => {
        const on = form.data.services.includes(id);
        const services = on ? form.data.services.filter((s) => s !== id) : [...form.data.services, id];
        const prices = { ...form.data.prices };
        if (on) {
            delete prices[id];
        } else if (prices[id] === undefined) {
            prices[id] = defaultPriceFor(id);
        }
        form.setData({ ...form.data, services, prices });
    };

    const save = (e: FormEvent) => {
        e.preventDefault();
        form.transform((data) => ({
            ...data,
            km: data.km === '' ? null : Number(data.km.replace(/\D/g, '')),
            labor: data.labor === '' ? null : data.labor,
            notes: data.notes === '' ? null : data.notes,
            prices: Object.fromEntries(
                Object.entries(data.prices)
                    .filter(([id]) => data.services.includes(Number(id)))
                    .map(([id, price]) => [id, price === '' ? null : price]),
            ),
        }));
        form.put(route('shop.visits.update', visit.id), { preserveScroll: true });
    };

    return (
        <ShopLayout shop={shop}>
            <Head title={t('visit_edit.title')} />

            <div className="mx-auto flex w-full flex-col gap-4 md:max-w-xl">
                <h1 className="text-xl font-extrabold">{t('visit_edit.title')}</h1>

                {/* Car context (read-only, with a link to edit the client details) */}
                <div className="border-primary bg-card rounded-2xl border-2 p-4 shadow-sm">
                    <div className="flex items-center justify-between">
                        <div className="text-[19px] font-extrabold">{visit.carLabel}</div>
                        <span className="bg-secondary text-secondary-foreground rounded-lg px-3 py-1 text-[15px] font-extrabold tracking-wider">
                            {visit.plate}
                        </span>
                    </div>
                    <div className="text-muted-foreground mt-1.5 text-base">
                        {visit.owner} · {visit.date}
                    </div>
                    <Link
                        href={route('shop.cars.edit', visit.carId)}
                        className="text-primary mt-2 flex w-fit items-center gap-1 text-[14px] font-bold"
                    >
                        <Pencil className="size-3.5" aria-hidden />
                        {t('car.edit')}
                    </Link>
                </div>

                <form onSubmit={save} className="flex flex-col gap-4">
                    <div>
                        <div className="mb-2 text-[17px] font-bold">
                            {t('visit.km_label')} <span className="text-destructive">*</span>
                        </div>
                        <input
                            inputMode="numeric"
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
                                    <button key={service.id} type="button" onClick={() => toggleService(service.id)} className={chipClasses(on)}>
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
                                    {oilTypes.map((type) => (
                                        <button
                                            key={type.key}
                                            type="button"
                                            onClick={() => form.setData('oil_type', type.key)}
                                            className={chipClasses(form.data.oil_type === type.key)}
                                        >
                                            {type.label}
                                        </button>
                                    ))}
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
                                                {brand}
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

                    <div>
                        <div className="mb-2 text-[17px] font-bold">{t('visit.notes')}</div>
                        <textarea
                            value={form.data.notes}
                            onChange={(e) => form.setData('notes', e.target.value)}
                            placeholder={t('visit.notes_placeholder')}
                            rows={3}
                            className="border-input bg-card focus-visible:border-ring w-full rounded-xl border-2 px-4 py-3 text-[17px] outline-none"
                        />
                    </div>

                    <button
                        type="submit"
                        disabled={form.processing}
                        className="bg-primary text-primary-foreground mt-1 h-16 rounded-2xl text-[22px] font-extrabold disabled:opacity-60"
                    >
                        {t('visit_edit.save')}
                    </button>
                </form>
            </div>
        </ShopLayout>
    );
}
