import ShopLayout from '@/layouts/shop-layout';
import { type Car, type Shop } from '@/types/shop';
import { Head, Link } from '@inertiajs/react';
import { Check, MessageCircle } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

interface Props {
    shop: Shop;
    car: Car;
    serviceTypes: string[];
    oilBrands: string[];
}

export default function NewVisit({ shop, car, serviceTypes, oilBrands }: Props) {
    const [services, setServices] = useState<Record<string, boolean>>({ 'تغيير زيت': true, 'فلتر زيت': true });
    const [newCust, setNewCust] = useState(false);
    const [km, setKm] = useState('');
    const [price, setPrice] = useState('');
    const [oil, setOil] = useState(oilBrands[0]);
    const [saved, setSaved] = useState(false);
    const [toast, setToast] = useState(false);
    const toastTimer = useRef<ReturnType<typeof setTimeout>>(null);

    useEffect(() => () => clearTimeout(toastTimer.current ?? undefined), []);

    const toggleService = (label: string) => setServices((s) => ({ ...s, [label]: !s[label] }));

    // Demo: save is client-side only until visits exist in the schema
    const save = () => {
        setSaved(true);
        setToast(true);
        clearTimeout(toastTimer.current ?? undefined);
        toastTimer.current = setTimeout(() => setToast(false), 5000);
    };

    const undo = () => {
        clearTimeout(toastTimer.current ?? undefined);
        setToast(false);
        setSaved(false);
    };

    const kmDigits = parseInt(km.replace(/\D/g, ''), 10);
    const kmLabel = Number.isNaN(kmDigits) ? '91,300' : kmDigits.toLocaleString('en-US');
    const nextKmLabel = Number.isNaN(kmDigits) ? '96,300' : (kmDigits + 5000).toLocaleString('en-US');
    const selectedServices = serviceTypes.filter((s) => services[s]);

    const summary = [
        `مرحباً ${car.owner}،`,
        `تمت اليوم صيانة ${car.label} — لوحة ${car.plate}:`,
        ...selectedServices.map((s) => `✅ ${s}${s === 'تغيير زيت' ? ` (${oil})` : ''}`),
        `قراءة العداد: ${kmLabel} كم`,
        `🔔 الموعد القادم: عند ${nextKmLabel} كم أو ${car.nextDue.date}`,
        `شكراً لثقتكم — ${shop.name}`,
    ].join('\n');
    const waHref = `https://wa.me/${car.whatsapp}?text=${encodeURIComponent(summary)}`;

    return (
        <ShopLayout shop={shop}>
            <Head title="زيارة جديدة" />

            {!saved && (
                <>
                    <h1 className="text-xl font-extrabold">زيارة جديدة</h1>

                    <div className="rounded-2xl border-2 border-primary bg-card p-4 shadow-sm">
                        <div className="flex items-center justify-between">
                            <div className="text-[19px] font-extrabold">{car.label}</div>
                            <span className="rounded-lg bg-secondary px-3 py-1 text-[15px] font-extrabold tracking-wider text-secondary-foreground">
                                {car.plate}
                            </span>
                        </div>
                        <div className="mt-1.5 text-base text-muted-foreground">
                            {car.owner} — {car.phone}
                        </div>
                        <div className="mt-1 text-[15px] text-muted-foreground">{car.lastService}</div>
                    </div>

                    <button
                        type="button"
                        onClick={() => setNewCust((v) => !v)}
                        className="min-h-12 self-start px-1 text-[15px] font-bold text-primary underline"
                    >
                        سيارة غير مسجلة؟
                    </button>

                    {newCust && (
                        <div className="flex flex-col gap-3 rounded-2xl bg-secondary p-4">
                            <div className="text-base font-extrabold text-secondary-foreground">زبون جديد</div>
                            <input
                                placeholder="الاسم"
                                className="h-14 w-full rounded-xl border-2 border-input bg-card px-4 text-lg outline-none focus-visible:border-ring"
                            />
                            <input
                                inputMode="tel"
                                placeholder="رقم الهاتف"
                                className="h-14 w-full rounded-xl border-2 border-input bg-card px-4 text-lg outline-none focus-visible:border-ring"
                            />
                        </div>
                    )}

                    <div>
                        <div className="mb-2 text-[17px] font-bold">
                            قراءة العداد الحالية <span className="text-destructive">*</span>
                        </div>
                        <input
                            inputMode="numeric"
                            placeholder="مثال: 91300"
                            value={km}
                            onChange={(e) => setKm(e.target.value)}
                            className="h-16 w-full rounded-2xl border-2 border-input bg-card px-4 text-center text-2xl font-bold tracking-wide outline-none focus-visible:border-ring"
                        />
                    </div>

                    <div>
                        <div className="mb-2 text-[17px] font-bold">الخدمات</div>
                        <div className="grid grid-cols-2 gap-2.5">
                            {serviceTypes.map((label) => {
                                const on = !!services[label];

                                return (
                                    <button
                                        key={label}
                                        type="button"
                                        onClick={() => toggleService(label)}
                                        className={`min-h-13 rounded-xl border-2 px-2 text-[17px] font-bold ${
                                            on
                                                ? 'border-primary bg-primary text-primary-foreground'
                                                : 'border-input bg-card text-foreground'
                                        }`}
                                    >
                                        {on ? '✓ ' : ''}
                                        {label}
                                    </button>
                                );
                            })}
                        </div>
                    </div>

                    <div>
                        <div className="mb-2 text-[17px] font-bold">نوع الزيت</div>
                        <select
                            value={oil}
                            onChange={(e) => setOil(e.target.value)}
                            className="h-14 w-full rounded-xl border-2 border-input bg-card px-3 text-[17px] text-foreground outline-none focus-visible:border-ring"
                        >
                            {oilBrands.map((brand, i) => (
                                <option key={brand} value={brand}>
                                    {i === 0 ? `نفس الزيارة السابقة — ${brand}` : brand}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div>
                        <div className="mb-2 text-[17px] font-bold">السعر (اختياري)</div>
                        <input
                            inputMode="decimal"
                            placeholder="د.أ"
                            value={price}
                            onChange={(e) => setPrice(e.target.value)}
                            className="h-14 w-full rounded-xl border-2 border-input bg-card px-4 text-center text-xl font-bold outline-none focus-visible:border-ring"
                        />
                    </div>

                    <button
                        type="button"
                        onClick={save}
                        className="mt-1 h-16 rounded-2xl bg-cta text-[22px] font-extrabold text-cta-foreground shadow-lg shadow-cta/35"
                    >
                        حفظ الزيارة
                    </button>
                </>
            )}

            {saved && (
                <>
                    <div className="rounded-[18px] border-2 border-success bg-success-soft px-4 py-6 text-center">
                        <div className="mx-auto flex size-16 items-center justify-center rounded-full bg-success text-success-foreground">
                            <Check className="size-9" aria-hidden />
                        </div>
                        <div className="mt-3 text-[21px] font-extrabold text-success-soft-foreground">تم حفظ الزيارة</div>
                        <div className="mt-1 text-[15px] text-success-soft-foreground/80">
                            {car.label} — {car.owner} — عداد {kmLabel} كم
                        </div>
                    </div>

                    <a
                        href={waHref}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="flex h-17 items-center justify-center gap-2 rounded-2xl bg-success text-[21px] font-extrabold text-success-foreground shadow-lg shadow-success/35"
                    >
                        <MessageCircle className="size-6" aria-hidden />
                        إرسال ملخص الزيارة واتساب
                    </a>

                    <div className="text-center text-sm text-muted-foreground">معاينة الرسالة</div>
                    <div className="ms-8 rounded-[18px] rounded-es-sm bg-[#dcf3d0] p-4 text-[15.5px] leading-8 whitespace-pre-line text-[#1e3325] shadow-sm">
                        {summary}
                        <div className="mt-1 text-end text-xs text-[#6b8a6f]">12:40 م</div>
                    </div>

                    <Link
                        href={route('shop.dashboard')}
                        className="flex h-14 items-center justify-center rounded-2xl border-2 border-input bg-card text-lg font-bold text-primary"
                    >
                        العودة للرئيسية
                    </Link>
                </>
            )}

            {toast && (
                <div className="fixed inset-x-0 bottom-24 z-20 mx-auto flex w-[calc(100%-2rem)] max-w-[26rem] items-center justify-between gap-3 rounded-2xl bg-foreground p-2 ps-4 shadow-xl">
                    <span className="text-base text-white">تم حفظ الزيارة</span>
                    <button
                        type="button"
                        onClick={undo}
                        className="h-12 rounded-xl bg-white/15 px-4 text-base font-extrabold text-[#ffc961]"
                    >
                        تراجع
                    </button>
                </div>
            )}
        </ShopLayout>
    );
}
