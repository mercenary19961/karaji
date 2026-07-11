import ShopLayout from '@/layouts/shop-layout';
import { useT } from '@/lib/i18n';
import { type SharedData } from '@/types';
import { type ServicePriceRow, type Shop } from '@/types/shop';
import { Head, useForm, usePage } from '@inertiajs/react';
import { type FormEvent } from 'react';

interface Props {
    shop: Shop;
    services: ServicePriceRow[];
}

export default function ServicePrices({ shop, services }: Props) {
    const { flash } = usePage<SharedData>().props;
    const t = useT();

    const form = useForm<{ prices: Record<number, string> }>({
        prices: Object.fromEntries(services.map((s) => [s.id, s.price])),
    });

    const save = (e: FormEvent) => {
        e.preventDefault();
        form.transform((data) => ({
            prices: Object.fromEntries(Object.entries(data.prices).map(([id, price]) => [id, price === '' ? null : price])),
        }));
        form.put(route('shop.service-prices.update'), { preserveScroll: true });
    };

    return (
        <ShopLayout shop={shop}>
            <Head title={t('prices.title')} />

            <div className="mx-auto flex w-full flex-col gap-4 md:max-w-xl">
                <h1 className="text-xl font-extrabold">{t('prices.title')}</h1>
                <p className="text-muted-foreground text-[15px] leading-relaxed">{t('prices.hint')}</p>

                {flash.success && (
                    <div className="bg-success-soft text-success-soft-foreground rounded-xl px-4 py-2.5 text-[15px] font-bold">{flash.success}</div>
                )}

                <form onSubmit={save} className="flex flex-col gap-4">
                    <div className="divide-border bg-card flex flex-col divide-y rounded-2xl p-2 shadow-sm">
                        {services.map((service) => (
                            <div key={service.id} className="flex items-center justify-between gap-3 px-2 py-2.5">
                                <span className="text-[17px] font-bold">{service.label}</span>
                                <div className="flex items-center gap-2">
                                    <input
                                        inputMode="decimal"
                                        value={form.data.prices[service.id] ?? ''}
                                        onChange={(e) => form.setData('prices', { ...form.data.prices, [service.id]: e.target.value })}
                                        placeholder="—"
                                        aria-label={service.label}
                                        className="border-input bg-card focus-visible:border-ring h-12 w-24 rounded-xl border-2 px-3 text-center text-lg font-bold outline-none"
                                    />
                                    <span className="text-muted-foreground w-8 text-[15px] font-bold">{t('common.currency')}</span>
                                </div>
                            </div>
                        ))}
                    </div>

                    <button
                        type="submit"
                        disabled={form.processing}
                        className="bg-primary text-primary-foreground h-14 rounded-xl text-[18px] font-extrabold disabled:opacity-60"
                    >
                        {t('prices.save')}
                    </button>
                </form>
            </div>
        </ShopLayout>
    );
}
