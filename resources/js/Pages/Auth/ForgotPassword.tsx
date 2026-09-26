import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { GuestLayout } from '@/Layouts/GuestLayout';
import { Input } from '@/Components/UI/Input';
import { Button } from '@/Components/UI/Button';
import { useTranslation } from '@/Hooks/useTranslation';

export default function ForgotPassword({ status }: { status?: string }) {
    const { t } = useTranslation();

    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/forgot-password');
    };

    return (
        <GuestLayout title={t('forgot_password')}>
            <Head title={t('forgot_password')} />

            <p className="text-sm text-slate-600 dark:text-slate-400 mb-6 text-center">
                {t('forgot_password_desc')}
            </p>

            {status && (
                <div className="mb-4 text-sm font-medium text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/30 p-3 rounded-xl border border-emerald-200 dark:border-emerald-800">
                    {status}
                </div>
            )}

            <form onSubmit={submit} className="space-y-4">
                <Input
                    label={t('email')}
                    type="email"
                    name="email"
                    value={data.email}
                    autoComplete="username"
                    autoFocus
                    onChange={(e) => setData('email', e.target.value)}
                    error={errors.email}
                    required
                />

                <Button type="submit" variant="primary" className="w-full" isLoading={processing}>
                    {t('send_reset_link')}
                </Button>

                <div className="text-center pt-2">
                    <Link href="/login" className="text-xs font-semibold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200">
                        &larr; {t('login')}
                    </Link>
                </div>
            </form>
        </GuestLayout>
    );
}
