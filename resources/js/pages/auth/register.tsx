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

interface RegisterForm {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
}

const copy = {
    ar: {
        title: 'إنشاء حساب',
        description: 'أدخل بياناتك لإنشاء حسابك',
        name: 'الاسم',
        namePlaceholder: 'الاسم الكامل',
        email: 'البريد الإلكتروني',
        password: 'كلمة المرور',
        confirm: 'تأكيد كلمة المرور',
        submit: 'إنشاء الحساب',
        haveAccount: 'عندك حساب؟',
        logIn: 'سجّل دخولك',
    },
    en: {
        title: 'Create an account',
        description: 'Enter your details below to create your account',
        name: 'Name',
        namePlaceholder: 'Full name',
        email: 'Email address',
        password: 'Password',
        confirm: 'Confirm password',
        submit: 'Create account',
        haveAccount: 'Already have an account?',
        logIn: 'Log in',
    },
};

export default function Register() {
    const { locale } = usePage<SharedData>().props;
    const t = locale === 'ar' ? copy.ar : copy.en;

    const { data, setData, post, processing, errors, reset } = useForm<RegisterForm>({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <AuthLayout title={t.title} description={t.description}>
            <Head title={t.title} />
            <form className="flex flex-col gap-6" onSubmit={submit}>
                <div className="grid gap-6">
                    <div className="grid gap-2">
                        <Label htmlFor="name">{t.name}</Label>
                        <Input
                            id="name"
                            type="text"
                            required
                            autoFocus
                            tabIndex={1}
                            autoComplete="name"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            disabled={processing}
                            placeholder={t.namePlaceholder}
                        />
                        <InputError message={errors.name} className="mt-2" />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="email">{t.email}</Label>
                        <Input
                            id="email"
                            type="email"
                            required
                            tabIndex={2}
                            autoComplete="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            disabled={processing}
                            placeholder="email@example.com"
                        />
                        <InputError message={errors.email} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password">{t.password}</Label>
                        <Input
                            id="password"
                            type="password"
                            required
                            tabIndex={3}
                            autoComplete="new-password"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            disabled={processing}
                            placeholder={t.password}
                        />
                        <InputError message={errors.password} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password_confirmation">{t.confirm}</Label>
                        <Input
                            id="password_confirmation"
                            type="password"
                            required
                            tabIndex={4}
                            autoComplete="new-password"
                            value={data.password_confirmation}
                            onChange={(e) => setData('password_confirmation', e.target.value)}
                            disabled={processing}
                            placeholder={t.confirm}
                        />
                        <InputError message={errors.password_confirmation} />
                    </div>

                    <Button type="submit" className="mt-2 w-full" tabIndex={5} disabled={processing}>
                        {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                        {t.submit}
                    </Button>
                </div>

                <div className="text-muted-foreground text-center text-sm">
                    {t.haveAccount}{' '}
                    <TextLink href={route('login')} tabIndex={6}>
                        {t.logIn}
                    </TextLink>
                </div>
            </form>
        </AuthLayout>
    );
}
