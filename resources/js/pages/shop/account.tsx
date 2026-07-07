import ShopLayout from '@/layouts/shop-layout';
import { type SharedData } from '@/types';
import { type Shop } from '@/types/shop';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { LogOut } from 'lucide-react';
import { type FormEvent } from 'react';

interface Props {
    shop: Shop;
    account: { name: string; email: string };
}

const inputClasses = 'border-input bg-card focus-visible:border-ring h-14 w-full rounded-xl border-2 px-4 text-lg outline-none';

function FieldError({ message }: { message?: string }) {
    if (!message) return null;

    return <div className="text-destructive mt-1.5 text-[15px] font-bold">{message}</div>;
}

export default function Account({ shop, account }: Props) {
    const { flash } = usePage<SharedData>().props;

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

    return (
        <ShopLayout shop={shop}>
            <Head title="حسابي" />

            <div className="mx-auto flex w-full flex-col gap-4 md:max-w-xl">
                <h1 className="text-xl font-extrabold">حسابي</h1>

                <div className="bg-card flex flex-col gap-1 rounded-2xl p-4 shadow-sm">
                    <div className="text-[17px] font-extrabold">{account.name}</div>
                    <div className="text-muted-foreground text-[15px]" dir="ltr">
                        {account.email}
                    </div>
                    <div className="text-muted-foreground mt-1 text-[15px]">
                        {shop.name} — {shop.area}
                    </div>
                </div>

                {flash.success && (
                    <div className="bg-success-soft text-success-soft-foreground rounded-xl px-4 py-2.5 text-[15px] font-bold">{flash.success}</div>
                )}

                <form onSubmit={changePassword} className="bg-card flex flex-col gap-3 rounded-2xl p-4 shadow-sm">
                    <div className="text-[17px] font-extrabold">تغيير كلمة المرور</div>

                    <div>
                        <input
                            type="password"
                            autoComplete="current-password"
                            placeholder="كلمة المرور الحالية"
                            value={form.data.current_password}
                            onChange={(e) => form.setData('current_password', e.target.value)}
                            className={inputClasses}
                        />
                        <FieldError message={form.errors.current_password} />
                    </div>

                    <div>
                        <input
                            type="password"
                            autoComplete="new-password"
                            placeholder="كلمة المرور الجديدة"
                            value={form.data.password}
                            onChange={(e) => form.setData('password', e.target.value)}
                            className={inputClasses}
                        />
                        <FieldError message={form.errors.password} />
                    </div>

                    <div>
                        <input
                            type="password"
                            autoComplete="new-password"
                            placeholder="تأكيد كلمة المرور الجديدة"
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
                        حفظ كلمة المرور
                    </button>
                </form>

                <button
                    type="button"
                    onClick={() => router.post(route('logout'))}
                    className="border-destructive/40 text-destructive bg-card flex h-14 items-center justify-center gap-2 rounded-2xl border-2 text-[18px] font-extrabold"
                >
                    <LogOut className="size-5" aria-hidden />
                    تسجيل الخروج
                </button>
            </div>
        </ShopLayout>
    );
}
