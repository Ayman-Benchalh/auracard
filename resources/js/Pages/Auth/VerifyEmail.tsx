import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { GuestLayout } from '@/Layouts/GuestLayout';
import { Button } from '@/Components/UI/Button';
import { useTranslation } from '@/Hooks/useTranslation';
import { MailCheck } from 'lucide-react';

export default function VerifyEmail({ status }: { status?: string }) {
    const { t } = useTranslation();

    const { post, processing } = useForm({});

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/email/verification-notification');
    };

    return (
        <GuestLayout title={t('verify_email')}>
            <Head title={t('verify_email')} />

            <div className="text-center space-y-4">
                <div className="w-16 h-16 rounded-2xl bg-[#6C5CE7]/15 text-[#6C5CE7] flex items-center justify-center mx-auto">
                    <MailCheck className="w-8 h-8" />
                </div>

                <p className="text-sm text-slate-600 dark:text-slate-400">
                    {t('verify_email_desc')}
                </p>

                {status === 'verification-link-sent' && (
                    <div className="text-sm font-medium text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/30 p-3 rounded-xl border border-emerald-200 dark:border-emerald-800">
                        A new verification link has been sent to your email address.
                    </div>
                )}

                <form onSubmit={submit} className="pt-2">
                    <Button type="submit" variant="primary" className="w-full" isLoading={processing}>
                        {t('resend_verification')}
                    </Button>
                </form>

                <div className="pt-4 border-t border-slate-200 dark:border-slate-800">
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        className="text-xs font-semibold text-rose-600 hover:underline"
                    >
                        {t('logout')}
                    </Link>
                </div>
            </div>
        </GuestLayout>
    );
}
