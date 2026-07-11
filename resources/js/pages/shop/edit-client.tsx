import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import ShopLayout from '@/layouts/shop-layout';
import { useT } from '@/lib/i18n';
import { type Shop } from '@/types/shop';
import { Head, useForm } from '@inertiajs/react';
import { type FormEvent } from 'react';

interface LicenseMonthOption {
    value: number;
    label: string;
}

interface Client {
    id: number;
    name: string;
    phone: string;
    plate: string;
    label: string | null;
    licenseMonth: number | null;
}

interface Props {
    shop: Shop;
    client: Client;
    licenseMonths: LicenseMonthOption[];
}

// Radix Select can't use an empty-string value, so "no license month" is a sentinel.
const NONE = 'none';
const inputClasses = 'border-input bg-card focus-visible:border-ring h-14 w-full rounded-xl border-2 px-4 text-lg outline-none';

function FieldError({ message }: { message?: string }) {
    if (!message) return null;

    return <div className="text-destructive mt-1.5 text-[15px] font-bold">{message}</div>;
}

export default function EditClient({ shop, client, licenseMonths }: Props) {
    const t = useT();

    const form = useForm({
        name: client.name,
        phone: client.phone,
        plate: client.plate,
        label: client.label ?? '',
        license_month: client.licenseMonth ? String(client.licenseMonth) : NONE,
    });

    const save = (e: FormEvent) => {
        e.preventDefault();
        form.transform((data) => ({
            ...data,
            license_month: data.license_month === NONE ? null : Number(data.license_month),
        }));
        form.put(route('shop.cars.update', client.id), { preserveScroll: true });
    };

    return (
        <ShopLayout shop={shop}>
            <Head title={t('client_edit.title')} />

            <div className="mx-auto flex w-full flex-col gap-4 md:max-w-xl">
                <h1 className="text-xl font-extrabold">{t('client_edit.title')}</h1>

                <form onSubmit={save} className="bg-card flex flex-col gap-3.5 rounded-2xl p-4 shadow-sm">
                    <div>
                        <label className="mb-1.5 block text-[15px] font-bold">{t('client_edit.name')}</label>
                        <input value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} className={inputClasses} />
                        <FieldError message={form.errors.name} />
                    </div>
                    <div>
                        <label className="mb-1.5 block text-[15px] font-bold">{t('client_edit.phone')}</label>
                        <input
                            inputMode="tel"
                            value={form.data.phone}
                            onChange={(e) => form.setData('phone', e.target.value)}
                            className={inputClasses}
                        />
                        <FieldError message={form.errors.phone} />
                    </div>
                    <div>
                        <label className="mb-1.5 block text-[15px] font-bold">{t('client_edit.plate')}</label>
                        <input
                            inputMode="numeric"
                            value={form.data.plate}
                            onChange={(e) => form.setData('plate', e.target.value)}
                            className={inputClasses}
                        />
                        <FieldError message={form.errors.plate} />
                    </div>
                    <div>
                        <label className="mb-1.5 block text-[15px] font-bold">{t('client_edit.car')}</label>
                        <input value={form.data.label} onChange={(e) => form.setData('label', e.target.value)} className={inputClasses} />
                    </div>
                    <div>
                        <label className="mb-1.5 block text-[15px] font-bold">{t('client_edit.license')}</label>
                        <Select value={form.data.license_month} onValueChange={(v) => form.setData('license_month', v)}>
                            <SelectTrigger className="h-14 text-[17px]">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value={NONE} className="text-[17px]">
                                    {t('client_edit.no_license')}
                                </SelectItem>
                                {licenseMonths.map((month) => (
                                    <SelectItem key={month.value} value={String(month.value)} className="text-[17px]">
                                        {month.value} · {month.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>

                    <button
                        type="submit"
                        disabled={form.processing}
                        className="bg-primary text-primary-foreground mt-1 h-14 rounded-xl text-[18px] font-extrabold disabled:opacity-60"
                    >
                        {t('client_edit.save')}
                    </button>
                </form>
            </div>
        </ShopLayout>
    );
}
