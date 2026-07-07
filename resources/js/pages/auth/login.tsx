import { Head, useForm, usePage } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler } from 'react';

import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';
import { type SharedData } from '@/types';

interface LoginForm {
    email: string;
    password: string;
    remember: boolean;
}

interface LoginProps {
    status?: string;
    canResetPassword: boolean;
    contactWhatsapp: string;
}

const copy = {
    ar: {
        title: 'سجّل دخولك',
        description: 'أدخل بريدك الإلكتروني وكلمة المرور للدخول',
        email: 'البريد الإلكتروني',
        password: 'كلمة المرور',
        forgot: 'نسيت كلمة المرور؟',
        remember: 'تذكّرني',
        submit: 'دخول',
        wantAccount: 'بدك تفتح حساب لمحلك؟',
        contactUs: 'تواصل معنا واتساب',
    },
    en: {
        title: 'Log in to your account',
        description: 'Enter your email and password below to log in',
        email: 'Email address',
        password: 'Password',
        forgot: 'Forgot password?',
        remember: 'Remember me',
        submit: 'Log in',
        wantAccount: 'Want an account for your shop?',
        contactUs: 'Contact us on WhatsApp',
    },
};

export default function Login({ status, canResetPassword, contactWhatsapp }: LoginProps) {
    const { locale } = usePage<SharedData>().props;
    const t = locale === 'ar' ? copy.ar : copy.en;

    const { data, setData, post, processing, errors, reset } = useForm<LoginForm>({
        email: '',
        password: '',
        remember: false,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <AuthLayout title={t.title} description={t.description}>
            <Head title={t.title} />

            <form className="flex flex-col gap-6" onSubmit={submit}>
                <div className="grid gap-6">
                    <div className="grid gap-2">
                        <Label htmlFor="email">{t.email}</Label>
                        <Input
                            id="email"
                            type="email"
                            required
                            autoFocus
                            tabIndex={1}
                            autoComplete="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            placeholder="email@example.com"
                        />
                        <InputError message={errors.email} />
                    </div>

                    <div className="grid gap-2">
                        <div className="flex items-center">
                            <Label htmlFor="password">{t.password}</Label>
                            {canResetPassword && (
                                <TextLink href={route('password.request')} className="ms-auto text-sm" tabIndex={5}>
                                    {t.forgot}
                                </TextLink>
                            )}
                        </div>
                        <Input
                            id="password"
                            type="password"
                            required
                            tabIndex={2}
                            autoComplete="current-password"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            placeholder={t.password}
                        />
                        <InputError message={errors.password} />
                    </div>

                    <div className="flex items-center gap-3">
                        <Checkbox id="remember" name="remember" tabIndex={3} />
                        <Label htmlFor="remember">{t.remember}</Label>
                    </div>

                    <Button type="submit" className="mt-4 w-full" tabIndex={4} disabled={processing}>
                        {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                        {t.submit}
                    </Button>
                </div>

                <div className="text-muted-foreground text-center text-sm">
                    {t.wantAccount}{' '}
                    <a
                        href={`https://wa.me/${contactWhatsapp}`}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="text-foreground font-medium underline underline-offset-4"
                    >
                        {t.contactUs}
                    </a>
                </div>
            </form>

            {status && <div className="mb-4 text-center text-sm font-medium text-green-600">{status}</div>}
        </AuthLayout>
    );
}
