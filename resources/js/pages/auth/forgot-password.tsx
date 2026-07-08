// Components
import { Head, useForm, usePage } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler } from 'react';

import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';
import { type SharedData } from '@/types';

const copy = {
    ar: {
        title: 'نسيت كلمة المرور',
        description: 'أدخل بريدك الإلكتروني لنرسل لك رابط إعادة التعيين',
        email: 'البريد الإلكتروني',
        submit: 'أرسل رابط إعادة التعيين',
        or: 'أو، ارجع إلى',
        logIn: 'تسجيل الدخول',
    },
    en: {
        title: 'Forgot password',
        description: 'Enter your email to receive a password reset link',
        email: 'Email address',
        submit: 'Email password reset link',
        or: 'Or, return to',
        logIn: 'log in',
    },
};

export default function ForgotPassword({ status }: { status?: string }) {
    const { locale } = usePage<SharedData>().props;
    const t = locale === 'ar' ? copy.ar : copy.en;

    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('password.email'));
    };

    return (
        <AuthLayout title={t.title} description={t.description}>
            <Head title={t.title} />

            {status && <div className="mb-4 text-center text-sm font-medium text-green-600">{status}</div>}

            <div className="space-y-6">
                <form onSubmit={submit}>
                    <div className="grid gap-2">
                        <Label htmlFor="email">{t.email}</Label>
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            autoComplete="off"
                            value={data.email}
                            autoFocus
                            onChange={(e) => setData('email', e.target.value)}
                            placeholder="email@example.com"
                        />

                        <InputError message={errors.email} />
                    </div>

                    <div className="my-6 flex items-center justify-start">
                        <Button className="w-full" disabled={processing}>
                            {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                            {t.submit}
                        </Button>
                    </div>
                </form>

                <div className="text-muted-foreground text-center text-sm">
                    <span>{t.or} </span>
                    <TextLink href={route('login')}>{t.logIn}</TextLink>
                </div>
            </div>
        </AuthLayout>
    );
}
