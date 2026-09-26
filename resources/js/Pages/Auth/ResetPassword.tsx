import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import { GuestLayout } from '@/Layouts/GuestLayout';
import { Input } from '@/Components/UI/Input';
import { Button } from '@/Components/UI/Button';
import { useTranslation } from '@/Hooks/useTranslation';

export default function ResetPassword({ token, email }: { token: string; email: string }) {
    const { t } = useTranslation();

    const { data, setData, post, processing, errors, reset } = useForm({
        token: token,
        email: email,
        password: '',
        password_confirmation: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/reset-password', {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <GuestLayout title={t('reset_password')}>
            <Head title={t('reset_password')} />

            <form onSubmit={submit} className="space-y-4">
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
                    label={t('password')}
                    type="password"
                    name="password"
                    value={data.password}
                    autoComplete="new-password"
                    autoFocus
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

                <Button type="submit" variant="primary" className="w-full" isLoading={processing}>
                    {t('reset_password')}
                </Button>
            </form>
        </GuestLayout>
    );
}
