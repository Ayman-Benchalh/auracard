import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { GuestLayout } from '@/Layouts/GuestLayout';
import { Input } from '@/Components/UI/Input';
import { Button } from '@/Components/UI/Button';
import { useTranslation } from '@/Hooks/useTranslation';

export default function Login({ status }: { status?: string }) {
    const { t } = useTranslation();

    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/login', {
            onFinish: () => reset('password'),
        });
    };

    return (
        <GuestLayout title={t('login')}>
            <Head title={t('login')} />

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

                <Input
                    label={t('password')}
                    type="password"
                    name="password"
                    value={data.password}
                    autoComplete="current-password"
                    onChange={(e) => setData('password', e.target.value)}
                    error={errors.password}
                    required
                />

                <div className="flex items-center justify-between text-sm">
                    <label className="flex items-center gap-2 cursor-pointer">
                        <input
                            type="checkbox"
                            name="remember"
                            checked={data.remember}
                            onChange={(e) => setData('remember', e.target.checked)}
                            className="rounded border-slate-300 text-[#6C5CE7] focus:ring-[#6C5CE7]"
                        />
                        <span className="text-slate-600 dark:text-slate-400">{t('remember_me')}</span>
                    </label>

                    <Link
                        href="/forgot-password"
                        className="text-xs font-semibold text-[#6C5CE7] hover:underline"
                    >
                        {t('forgot_password')}
                    </Link>
                </div>

                <Button type="submit" variant="primary" className="w-full" isLoading={processing}>
                    {t('login')}
                </Button>

                <div className="pt-4 text-center text-sm text-slate-500 dark:text-slate-400 border-t border-slate-200 dark:border-slate-800">
                    {t('dont_have_account')}{' '}
                    <Link href="/register" className="font-bold text-[#6C5CE7] hover:underline">
                        {t('register')}
                    </Link>
                </div>
            </form>
        </GuestLayout>
    );
}
