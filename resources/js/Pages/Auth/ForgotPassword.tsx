import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { Mail, ArrowLeft } from 'lucide-react';
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

            <p className="text-sm text-slate-600 dark:text-slate-400 mb-6 text-center sm:text-start leading-relaxed">
                {t('forgot_password_desc')}
            </p>

            {status && (
                <div className="mb-5 text-sm font-medium text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 p-3.5 rounded-xl border border-emerald-200 dark:border-emerald-800">
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
                    placeholder="nom@exemple.ma"
                    startSlot={<Mail className="w-4 h-4" />}
                    onChange={(e) => setData('email', e.target.value)}
                    error={errors.email}
                    required
                />

                <Button
                    type="submit"
                    variant="primary"
                    className="w-full min-h-[44px] text-sm font-semibold tracking-wide"
                    isLoading={processing}
                >
                    {t('send_reset_link')}
                </Button>

                <div className="text-center pt-2">
                    <Link
                        href="/login"
                        className="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 transition-colors"
                    >
                        <ArrowLeft className="w-3.5 h-3.5 rtl:rotate-180" />
                        <span>{t('login')}</span>
                    </Link>
                </div>
            </form>
        </GuestLayout>
    );
}
