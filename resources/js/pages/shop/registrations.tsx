import ShopLayout from '@/layouts/shop-layout';
import { useT } from '@/lib/i18n';
import { type SharedData } from '@/types';
import { type Shop } from '@/types/shop';
import { Head, router, usePage, usePoll } from '@inertiajs/react';
import { Check, Copy, X } from 'lucide-react';
import { QRCodeSVG } from 'qrcode.react';
import { useState } from 'react';

interface PendingItem {
    id: number;
    name: string;
    phone: string;
    plate: string;
    label: string | null;
    ago: string;
}

interface Props {
    shop: Shop;
    joinUrl: string;
    autoAccept: boolean;
    pending: PendingItem[];
}

export default function Registrations({ shop, joinUrl, autoAccept, pending }: Props) {
    const { flash } = usePage<SharedData>().props;
    const t = useT();
    const [copied, setCopied] = useState(false);

    // New customer requests appear on their own — no refresh needed
    usePoll(15000, { only: ['pending'] });

    const copyLink = async () => {
        try {
            await navigator.clipboard.writeText(joinUrl);
            setCopied(true);
            setTimeout(() => setCopied(false), 1800);
        } catch {
            // clipboard unavailable — leave the link visible for manual copy
        }
    };

    const accept = (id: number) => router.post(route('shop.registrations.accept', id), {}, { preserveScroll: true });
    const reject = (id: number) => router.delete(route('shop.registrations.reject', id), { preserveScroll: true });

    return (
        <ShopLayout shop={shop}>
            <Head title={t('reg.title')} />

            <div className="mx-auto flex w-full flex-col gap-4 md:max-w-2xl">
                <h1 className="text-xl font-extrabold">{t('reg.title')}</h1>

                {flash.success && (
                    <div className="bg-success-soft text-success-soft-foreground rounded-xl px-4 py-2.5 text-[15px] font-bold">{flash.success}</div>
                )}

                {/* QR to hold up to the customer */}
                <div className="bg-card flex flex-col items-center gap-3 rounded-2xl p-5 shadow-sm">
                    <div className="rounded-2xl bg-white p-3 shadow-sm">
                        <QRCodeSVG value={joinUrl} size={196} bgColor="#ffffff" fgColor="#13324d" />
                    </div>
                    <p className="text-muted-foreground max-w-xs text-center text-[15px]">{t('reg.qr_hint')}</p>
                    <button
                        type="button"
                        onClick={copyLink}
                        className="bg-secondary text-secondary-foreground flex h-12 items-center gap-2 rounded-xl px-4 text-[15px] font-bold"
                    >
                        {copied ? <Check className="size-4" aria-hidden /> : <Copy className="size-4" aria-hidden />}
                        {copied ? t('reg.copied') : t('reg.copy_link')}
                    </button>
                    <div className={`text-[13.5px] font-bold ${autoAccept ? 'text-success-soft-foreground' : 'text-muted-foreground'}`}>
                        {autoAccept ? t('reg.auto_on') : t('reg.auto_off')}
                    </div>
                </div>

                {/* Pending queue */}
                <div className="bg-card rounded-2xl p-4 shadow-sm">
                    <h2 className="mb-3 text-[17px] font-extrabold">
                        {t('reg.pending_title')} {pending.length > 0 && <span className="text-cta">({pending.length})</span>}
                    </h2>
                    {pending.length === 0 ? (
                        <div className="text-muted-foreground text-base">{t('reg.none')}</div>
                    ) : (
                        <div className="flex flex-col gap-2.5">
                            {pending.map((item) => (
                                <div key={item.id} className="border-border flex flex-col gap-3 rounded-xl border p-3.5">
                                    <div className="flex items-start justify-between gap-2">
                                        <div className="flex flex-col gap-1.5 text-[15px]">
                                            <div className="flex gap-2">
                                                <span className="text-muted-foreground w-16 shrink-0 font-medium">{t('reg.field_name')}</span>
                                                <span className="font-bold">{item.name}</span>
                                            </div>
                                            <div className="flex gap-2">
                                                <span className="text-muted-foreground w-16 shrink-0 font-medium">{t('reg.field_phone')}</span>
                                                <span className="font-bold" dir="ltr">
                                                    {item.phone}
                                                </span>
                                            </div>
                                            <div className="flex gap-2">
                                                <span className="text-muted-foreground w-16 shrink-0 font-medium">{t('reg.field_plate')}</span>
                                                <span className="font-bold" dir="ltr">
                                                    {item.plate}
                                                </span>
                                            </div>
                                            {item.label && (
                                                <div className="flex gap-2">
                                                    <span className="text-muted-foreground w-16 shrink-0 font-medium">{t('reg.field_car')}</span>
                                                    <span className="font-bold">{item.label}</span>
                                                </div>
                                            )}
                                        </div>
                                        <span className="text-muted-foreground shrink-0 text-xs">{item.ago}</span>
                                    </div>
                                    <div className="flex gap-2">
                                        <button
                                            type="button"
                                            onClick={() => accept(item.id)}
                                            className="bg-success text-success-foreground flex h-12 flex-1 items-center justify-center gap-1.5 rounded-xl text-[15px] font-bold"
                                        >
                                            <Check className="size-4" aria-hidden />
                                            {t('reg.accept')}
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => reject(item.id)}
                                            className="border-destructive/40 text-destructive bg-card flex h-12 items-center justify-center gap-1.5 rounded-xl border-2 px-5 text-[15px] font-bold"
                                        >
                                            <X className="size-4" aria-hidden />
                                            {t('reg.reject')}
                                        </button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </ShopLayout>
    );
}
