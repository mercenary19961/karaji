import { PasswordInput } from '@/components/password-input';
import ShopLayout from '@/layouts/shop-layout';
import { useT } from '@/lib/i18n';
import { type SharedData } from '@/types';
import { type Shop } from '@/types/shop';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { Camera, ChevronLeft, Coins, LogOut, QrCode, UserRound } from 'lucide-react';
import { type ChangeEvent, type FormEvent } from 'react';

interface Props {
    shop: Shop;
    account: { name: string; email: string };
    autoAccept: boolean;
}

// Matches the shop portal's field style; the ring-0 overrides drop PasswordInput's
// shadcn focus ring so it reads like the other shop inputs (border-colour focus).
const inputClasses =
    'border-input bg-card focus-visible:border-ring focus-visible:ring-0 focus-visible:ring-offset-0 h-14 w-full rounded-xl border-2 px-4 text-lg outline-none';

function FieldError({ message }: { message?: string }) {
    if (!message) return null;

    return <div className="text-destructive mt-1.5 text-[15px] font-bold">{message}</div>;
}

export default function Account({ shop, account, autoAccept }: Props) {
    const { flash, auth, errors } = usePage<SharedData>().props;
    const t = useT();
    const avatarUrl = auth.user.avatar_url;
    const avatarError = (errors as Record<string, string>)?.avatar;

    const toggleAutoAccept = () => router.put(route('shop.account.settings'), { auto_accept_registrations: !autoAccept }, { preserveScroll: true });

    const form = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const changePassword = (e: FormEvent) => {
        e.preventDefault();
        form.put(route('shop.account.password'), {
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
    };

    const uploadAvatar = (e: ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        e.target.value = '';
        if (!file) return;
        router.post(route('shop.account.avatar'), { avatar: file }, { preserveScroll: true, forceFormData: true });
    };

    return (
        <ShopLayout shop={shop}>
            <Head title={t('acct.title')} />

            <div className="mx-auto flex w-full flex-col gap-4 md:max-w-xl">
                <h1 className="text-xl font-extrabold">{t('acct.title')}</h1>

                <div className="bg-card flex items-center gap-4 rounded-2xl p-4 shadow-sm">
                    <div className="bg-muted text-muted-foreground flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-full">
                        {avatarUrl ? <img src={avatarUrl} alt="" className="size-full object-cover" /> : <UserRound className="size-9" aria-hidden />}
                    </div>
                    <div className="min-w-0">
                        <div className="text-[17px] font-extrabold">{account.name}</div>
                        <div className="text-muted-foreground truncate text-[15px]" dir="ltr">
                            {account.email}
                        </div>
                        <div className="text-muted-foreground mt-1 text-[15px]">
                            {shop.name} · {shop.area}
                        </div>
                    </div>
                </div>

                <div className="flex gap-2.5">
                    <label className="bg-secondary text-secondary-foreground flex h-12 flex-1 cursor-pointer items-center justify-center gap-2 rounded-xl text-[16px] font-bold">
                        <Camera className="size-5" aria-hidden />
                        {t('acct.change_picture')}
                        <input type="file" accept="image/png,image/jpeg,image/webp" onChange={uploadAvatar} className="hidden" />
                    </label>
                    {avatarUrl && (
                        <button
                            type="button"
                            onClick={() => router.delete(route('shop.account.avatar.delete'), { preserveScroll: true })}
                            className="text-destructive border-destructive/40 bg-card h-12 rounded-xl border-2 px-4 text-[16px] font-bold"
                        >
                            {t('acct.remove_picture')}
                        </button>
                    )}
                </div>
                {avatarError && <div className="text-destructive -mt-1 text-[15px] font-bold">{avatarError}</div>}

                {flash.success && (
                    <div className="bg-success-soft text-success-soft-foreground rounded-xl px-4 py-2.5 text-[15px] font-bold">{flash.success}</div>
                )}

                <form onSubmit={changePassword} className="bg-card flex flex-col gap-3 rounded-2xl p-4 shadow-sm">
                    <div className="text-[17px] font-extrabold">{t('acct.change_password')}</div>

                    <div>
                        <PasswordInput
                            autoComplete="current-password"
                            placeholder={t('acct.current_pw')}
                            value={form.data.current_password}
                            onChange={(e) => form.setData('current_password', e.target.value)}
                            className={inputClasses}
                        />
                        <FieldError message={form.errors.current_password} />
                    </div>

                    <div>
                        <PasswordInput
                            autoComplete="new-password"
                            placeholder={t('acct.new_pw')}
                            value={form.data.password}
                            onChange={(e) => form.setData('password', e.target.value)}
                            className={inputClasses}
                        />
                        <FieldError message={form.errors.password} />
                    </div>

                    <div>
                        <PasswordInput
                            autoComplete="new-password"
                            placeholder={t('acct.confirm_pw')}
                            value={form.data.password_confirmation}
                            onChange={(e) => form.setData('password_confirmation', e.target.value)}
                            className={inputClasses}
                        />
                    </div>

                    <button
                        type="submit"
                        disabled={form.processing}
                        className="bg-primary text-primary-foreground h-14 rounded-xl text-[18px] font-extrabold disabled:opacity-60"
                    >
                        {t('acct.save_password')}
                    </button>
                </form>

                <Link
                    href={route('shop.service-prices')}
                    className="bg-card flex h-14 items-center justify-between rounded-2xl px-4 text-[17px] font-bold shadow-sm"
                >
                    <span className="flex items-center gap-2.5">
                        <Coins className="text-primary size-5" aria-hidden />
                        {t('nav.prices')}
                    </span>
                    <ChevronLeft className="text-muted-foreground size-5 ltr:-scale-x-100" aria-hidden />
                </Link>

                <Link
                    href={route('shop.registrations')}
                    className="bg-card flex h-14 items-center justify-between rounded-2xl px-4 text-[17px] font-bold shadow-sm"
                >
                    <span className="flex items-center gap-2.5">
                        <QrCode className="text-primary size-5" aria-hidden />
                        {t('nav.registrations')}
                    </span>
                    <ChevronLeft className="text-muted-foreground size-5 ltr:-scale-x-100" aria-hidden />
                </Link>

                {/* Auto-accept toggle for QR sign-up requests */}
                <div className="bg-card flex items-center justify-between gap-3 rounded-2xl p-4 shadow-sm">
                    <div className="min-w-0">
                        <div className="text-[16px] font-bold">{t('acct.auto_accept')}</div>
                        <div className="text-muted-foreground mt-0.5 text-[13.5px] leading-snug">{t('acct.auto_accept_hint')}</div>
                    </div>
                    <button
                        type="button"
                        role="switch"
                        aria-checked={autoAccept}
                        aria-label={t('acct.auto_accept')}
                        onClick={toggleAutoAccept}
                        className={`relative h-7 w-12 shrink-0 rounded-full transition-colors ${autoAccept ? 'bg-success' : 'bg-input'}`}
                    >
                        <span className={`absolute top-1 size-5 rounded-full bg-white shadow transition-all ${autoAccept ? 'start-6' : 'start-1'}`} />
                    </button>
                </div>

                <button
                    type="button"
                    onClick={() => router.post(route('logout'))}
                    className="border-destructive/40 text-destructive bg-card flex h-14 items-center justify-center gap-2 rounded-2xl border-2 text-[18px] font-extrabold"
                >
                    <LogOut className="size-5" aria-hidden />
                    {t('nav.logout')}
                </button>
            </div>
        </ShopLayout>
    );
}
