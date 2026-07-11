import { type SharedData } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/react';
import { CheckCircle2 } from 'lucide-react';
import { type FormEvent } from 'react';

interface Props {
    shopName: string;
    token: string;
}

// Customer-facing = Arabic (like the WhatsApp templates), independent of any UI locale.
const inputClasses = 'border-input bg-card focus-visible:border-ring h-14 w-full rounded-xl border-2 px-4 text-lg outline-none';

function FieldError({ message }: { message?: string }) {
    if (!message) return null;

    return <div className="text-destructive mt-1.5 text-[15px] font-bold">{message}</div>;
}

export default function Join({ shopName, token }: Props) {
    const { flash, name } = usePage<SharedData>().props;

    const form = useForm({ name: '', phone: '', plate: '', label: '' });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        form.post(route('join.store', token), { preserveScroll: true });
    };

    return (
        <div dir="rtl" className="bg-muted flex min-h-screen flex-col items-center px-4 py-10">
            <Head title={`تسجيل سيارة · ${shopName}`} />

            <div className="w-full max-w-md">
                <div className="mb-5 text-center">
                    <div className="text-primary text-3xl font-extrabold tracking-wide">{name}</div>
                    <div className="text-muted-foreground mt-1 text-[15px] font-medium">{shopName}</div>
                </div>

                {flash.success ? (
                    <div className="bg-card rounded-2xl p-6 text-center shadow-sm">
                        <div className="bg-success text-success-foreground mx-auto flex size-16 items-center justify-center rounded-full">
                            <CheckCircle2 className="size-9" aria-hidden />
                        </div>
                        <div className="text-success-soft-foreground mt-3 text-[20px] font-extrabold">{flash.success}</div>
                        <div className="text-muted-foreground mt-1.5 text-[15px]">فيك تسكّر الصفحة · رح نتواصل معك بمواعيد الصيانة</div>
                    </div>
                ) : (
                    <form onSubmit={submit} className="bg-card flex flex-col gap-3 rounded-2xl p-5 shadow-sm">
                        <div className="text-[18px] font-extrabold">سجّل سيارتك</div>
                        <p className="text-muted-foreground -mt-1.5 text-[14.5px]">اكتب معلوماتك ومنذكّرك بمواعيد الصيانة وتغيير الزيت</p>

                        <div>
                            <input
                                placeholder="اسمك"
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
                            placeholder="نوع السيارة (اختياري)"
                            value={form.data.label}
                            onChange={(e) => form.setData('label', e.target.value)}
                            className={inputClasses}
                        />

                        <button
                            type="submit"
                            disabled={form.processing}
                            className="bg-cta text-cta-foreground mt-1 h-15 rounded-2xl text-[20px] font-extrabold disabled:opacity-60"
                        >
                            أرسل
                        </button>
                    </form>
                )}
            </div>
        </div>
    );
}
