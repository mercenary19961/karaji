import ShopLayout from '@/layouts/shop-layout';
import { type SharedData } from '@/types';
import { type FormCar, type OilTypeOption, type SavedVisit, type ServiceTypeOption, type Shop } from '@/types/shop';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { Check, MessageCircle, Search } from 'lucide-react';
import { type FormEvent, useState } from 'react';

interface Props {
    shop: Shop;
    car: FormCar | null;
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

export default function NewVisit({ shop, car, serviceTypes, oilBrands, oilTypes, savedVisit }: Props) {
    const { flash } = usePage<SharedData>().props;

    const oilChangeId = serviceTypes.find((s) => s.name === 'تغيير زيت')?.id;
    const defaultServices = serviceTypes.filter((s) => s.name === 'تغيير زيت' || s.name === 'فلتر زيت').map((s) => s.id);
    const [newCust, setNewCust] = useState(false);
    const [q, setQ] = useState('');

    const form = useForm({
        name: '',
        phone: '',
        plate: '',
        label: '',
        km: '',
        services: defaultServices,
        oil_brand: car?.lastOilBrand ?? oilBrands[0],
        oil_type: car?.lastOilType ?? oilTypes[0]?.key ?? '',
        price: '',
    });

    // The oil-type control only matters when this visit changes the oil
    const oilChangeSelected = oilChangeId !== undefined && form.data.services.includes(oilChangeId);

    const searchCar = (e: FormEvent) => {
        e.preventDefault();
        router.get(route('shop.cars.search'), { q, to: 'visit' });
    };

    const toggleService = (id: number) =>
        form.setData('services', form.data.services.includes(id) ? form.data.services.filter((s) => s !== id) : [...form.data.services, id]);

    const save = (e: FormEvent) => {
        e.preventDefault();
        form.transform((data) => ({
            ...data,
            car_id: newCust ? null : (car?.id ?? null),
            km: data.km === '' ? null : Number(data.km.replace(/\D/g, '')),
            price: data.price === '' ? null : data.price,
        }));
        form.post(route('shop.visits.store'), { preserveScroll: true });
    };

    // ===== Post-save success state (the digital windshield sticker) =====
    if (savedVisit) {
        const summary = [
            `مرحبا ${savedVisit.owner}،`,
            `عملنا اليوم صيانة ${savedVisit.carLabel} (لوحة ${savedVisit.plate}):`,
            ...savedVisit.services.map((s) => `✅ ${s}${s === 'تغيير زيت' && savedVisit.oilBrand ? ` (${savedVisit.oilBrand})` : ''}`),
            `قراءة العداد: ${savedVisit.km} كم`,
            ...(savedVisit.nextDueKm ? [`🔔 الموعد الجاي: عند ${savedVisit.nextDueKm} كم أو ${savedVisit.nextDueDate}`] : []),
            `شكراً لثقتك 🙏 ${shop.name}`,
        ].join('\n');
        const waHref = `https://wa.me/${savedVisit.whatsapp}?text=${encodeURIComponent(summary)}`;

        return (
            <ShopLayout shop={shop}>
                <Head title="تم حفظ الزيارة" />

                <div className="mx-auto flex w-full flex-col gap-4 md:max-w-xl">
                    <div className="border-success bg-success-soft rounded-[18px] border-2 px-4 py-6 text-center">
                        <div className="bg-success text-success-foreground mx-auto flex size-16 items-center justify-center rounded-full">
                            <Check className="size-9" aria-hidden />
                        </div>
                        <div className="text-success-soft-foreground mt-3 text-[21px] font-extrabold">انحفظت الزيارة ✓</div>
                        <div className="text-success-soft-foreground/80 mt-1 text-[15px]">
                            {savedVisit.carLabel} · {savedVisit.owner} · عداد {savedVisit.km} كم
                        </div>
                    </div>

                    <a
                        href={waHref}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="bg-success text-success-foreground shadow-success/35 flex h-17 items-center justify-center gap-2 rounded-2xl text-[21px] font-extrabold shadow-lg"
                    >
                        <MessageCircle className="size-6" aria-hidden />
                        ابعت ملخص الزيارة واتساب
                    </a>

                    <div className="text-muted-foreground text-center text-sm">شوف الرسالة</div>
                    <div className="ms-8 rounded-[18px] rounded-es-sm bg-[#dcf3d0] p-4 text-[15.5px] leading-8 whitespace-pre-line text-[#1e3325] shadow-sm">
                        {summary}
                    </div>

                    <Link
                        href={route('shop.dashboard')}
                        className="border-input bg-card text-primary flex h-14 items-center justify-center rounded-2xl border-2 text-lg font-bold"
                    >
                        ارجع للرئيسية
                    </Link>

                    <button
                        type="button"
                        onClick={() => router.delete(route('shop.visits.destroy', savedVisit.id))}
                        className="text-destructive min-h-12 text-center text-[15px] font-bold underline"
                    >
                        تراجع عن الحفظ
                    </button>
                </div>
            </ShopLayout>
        );
    }

    // ===== Entry form =====
    return (
        <ShopLayout shop={shop}>
            <Head title="زيارة جديدة" />

            <div className="mx-auto flex w-full flex-col gap-4 md:max-w-xl">
                <h1 className="text-xl font-extrabold">زيارة جديدة</h1>

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
                    </div>
                )}

                {!newCust && !car && (
                    <form onSubmit={searchCar}>
                        <div className="relative">
                            <Search className="text-muted-foreground absolute start-4 top-1/2 size-5 -translate-y-1/2" aria-hidden />
                            <input
                                inputMode="numeric"
                                placeholder="دوّر ع السيارة برقم اللوحة أو التلفون"
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
                    {newCust ? 'ارجع لسيارة مسجلة' : 'سيارة مش مسجلة؟'}
                </button>

                <form onSubmit={save} className="flex flex-col gap-4">
                    {newCust && (
                        <div className="bg-secondary flex flex-col gap-3 rounded-2xl p-4">
                            <div className="text-secondary-foreground text-base font-extrabold">زبون جديد</div>
                            <div>
                                <input
                                    placeholder="الاسم"
                                    value={form.data.name}
                                    onChange={(e) => form.setData('name', e.target.value)}
                                    className={inputClasses}
                                />
                                <FieldError message={form.errors.name} />
                            </div>
                            <div>
                                <input
                                    inputMode="tel"
                                    placeholder="رقم التلفون"
                                    value={form.data.phone}
                                    onChange={(e) => form.setData('phone', e.target.value)}
                                    className={inputClasses}
                                />
                                <FieldError message={form.errors.phone} />
                            </div>
                            <div>
                                <input
                                    inputMode="numeric"
                                    placeholder="رقم اللوحة"
                                    value={form.data.plate}
                                    onChange={(e) => form.setData('plate', e.target.value)}
                                    className={inputClasses}
                                />
                                <FieldError message={form.errors.plate} />
                            </div>
                            <input
                                placeholder="السيارة (مثلاً كيا سبورتاج 2019) · اختياري"
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
                                    قراءة العداد الحالية <span className="text-destructive">*</span>
                                </div>
                                <input
                                    inputMode="numeric"
                                    placeholder="مثلاً 91300"
                                    value={form.data.km}
                                    onChange={(e) => form.setData('km', e.target.value)}
                                    className="border-input bg-card focus-visible:border-ring h-16 w-full rounded-2xl border-2 px-4 text-center text-2xl font-bold tracking-wide outline-none"
                                />
                                <FieldError message={form.errors.km} />
                            </div>

                            <div>
                                <div className="mb-2 text-[17px] font-bold">الخدمات</div>
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
                                                {service.name}
                                            </button>
                                        );
                                    })}
                                </div>
                                <FieldError message={form.errors.services} />
                            </div>

                            {oilChangeSelected && (
                                <>
                                    <div>
                                        <div className="mb-2 text-[17px] font-bold">نوع الزيت</div>
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
                                        <div className="mb-2 text-[17px] font-bold">ماركة الزيت</div>
                                        <select
                                            value={form.data.oil_brand}
                                            onChange={(e) => form.setData('oil_brand', e.target.value)}
                                            className="border-input bg-card text-foreground focus-visible:border-ring h-14 w-full rounded-xl border-2 px-3 text-[17px] outline-none"
                                        >
                                            {oilBrands.map((brand) => (
                                                <option key={brand} value={brand}>
                                                    {brand === car?.lastOilBrand ? `زي آخر زيارة · ${brand}` : brand}
                                                </option>
                                            ))}
                                        </select>
                                    </div>
                                </>
                            )}

                            <div>
                                <div className="mb-2 text-[17px] font-bold">السعر (اختياري)</div>
                                <input
                                    inputMode="decimal"
                                    placeholder="د.أ"
                                    value={form.data.price}
                                    onChange={(e) => form.setData('price', e.target.value)}
                                    className="border-input bg-card focus-visible:border-ring h-14 w-full rounded-xl border-2 px-4 text-center text-xl font-bold outline-none"
                                />
                            </div>

                            <button
                                type="submit"
                                disabled={form.processing}
                                className="bg-cta text-cta-foreground shadow-cta/35 mt-1 h-16 rounded-2xl text-[22px] font-extrabold shadow-lg disabled:opacity-60"
                            >
                                حفظ الزيارة
                            </button>
                        </>
                    )}
                </form>
            </div>
        </ShopLayout>
    );
}
