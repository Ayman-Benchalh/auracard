import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { GuestLayout } from '@/Layouts/GuestLayout';
import { Input } from '@/Components/UI/Input';
import { Button } from '@/Components/UI/Button';
import { useTranslation } from '@/Hooks/useTranslation';

export default function Register() {
    const { t } = useTranslation();

    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        phone: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/register', {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <GuestLayout title={t('register')}>
            <Head title={t('register')} />

            <form onSubmit={submit} className="space-y-4">
                <Input
                    label={t('name')}
                    type="text"
                    name="name"
                    value={data.name}
                    autoComplete="name"
                    autoFocus
                    onChange={(e) => setData('name', e.target.value)}
                    error={errors.name}
                    required
                />

                <Input
                    label={t('email')}
                    type="email"
                    name="email"
                    value={data.email}
                    autoComplete="username"
                    onChange={(e) => setData('email', e.target.value)}
                    error={errors.email}
                    required
                />

                <Input
                    label={t('phone')}
                    type="tel"
                    name="phone"
                    value={data.phone}
                    autoComplete="tel"
                    placeholder="+212 600 000 000"
                    onChange={(e) => setData('phone', e.target.value)}
                    error={errors.phone}
                />

                <Input
                    label={t('password')}
                    type="password"
                    name="password"
                    value={data.password}
                    autoComplete="new-password"
                    onChange={(e) => setData('password', e.target.value)}
                    error={errors.password}
                    required
                />

                <Input
                    label={t('password_confirmation')}
                    type="password"
                    name="password_confirmation"
                    value={data.password_confirmation}
                    autoComplete="new-password"
                    onChange={(e) => setData('password_confirmation', e.target.value)}
                    error={errors.password_confirmation}
                    required
                />

                <Button type="submit" variant="primary" className="w-full mt-2" isLoading={processing}>
                    {t('register')}
                </Button>

                <div className="pt-4 text-center text-sm text-slate-500 dark:text-slate-400 border-t border-slate-200 dark:border-slate-800">
                    {t('already_registered')}{' '}
                    <Link href="/login" className="font-bold text-[#6C5CE7] hover:underline">
                        {t('login')}
                    </Link>
                </div>
            </form>
        </GuestLayout>
    );
}
