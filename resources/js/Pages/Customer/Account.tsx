import React from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import { AppLayout } from '@/Layouts/AppLayout';
import { Card } from '@/Components/UI/Card';
import { Input } from '@/Components/UI/Input';
import { Button } from '@/Components/UI/Button';
import { PageProps } from '@/types';
import { useTranslation } from '@/Hooks/useTranslation';
import { User, Lock, Globe, Shield } from 'lucide-react';

export default function Account({ status }: { status?: string }) {
    const { auth } = usePage<PageProps>().props;
    const { t } = useTranslation();

    const profileForm = useForm({
        name: auth.user?.name || '',
        email: auth.user?.email || '',
        phone: auth.user?.phone || '',
        locale: auth.user?.locale || 'fr',
    });

    const passwordForm = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const submitProfile = (e: React.FormEvent) => {
        e.preventDefault();
        profileForm.patch('/account');
    };

    const submitPassword = (e: React.FormEvent) => {
        e.preventDefault();
        passwordForm.put('/password', {
            onSuccess: () => passwordForm.reset(),
        });
    };

    return (
        <AppLayout title={t('account')}>
            <Head title={t('account')} />

            <div className="max-w-4xl space-y-8">
                {status === 'account-updated' && (
                    <div className="text-sm font-medium text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/30 p-4 rounded-2xl border border-emerald-200 dark:border-emerald-800">
                        {t('saved')}
                    </div>
                )}

                {status === 'password-updated' && (
                    <div className="text-sm font-medium text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/30 p-4 rounded-2xl border border-emerald-200 dark:border-emerald-800">
                        Password updated successfully.
                    </div>
                )}

                {/* Profile Information */}
                <Card className="space-y-6">
                    <div className="flex items-center gap-3 border-b border-slate-100 dark:border-slate-800 pb-4">
                        <div className="w-10 h-10 rounded-xl bg-[#6C5CE7]/15 text-[#6C5CE7] flex items-center justify-center">
                            <User className="w-5 h-5" />
                        </div>
                        <div>
                            <h3 className="font-bold text-base text-slate-900 dark:text-white">
                                Account Information
                            </h3>
                            <p className="text-xs text-slate-500">Update your account name, email and phone</p>
                        </div>
                    </div>

                    <form onSubmit={submitProfile} className="space-y-4 max-w-xl">
                        <Input
                            label={t('name')}
                            type="text"
                            value={profileForm.data.name}
                            onChange={(e) => profileForm.setData('name', e.target.value)}
                            error={profileForm.errors.name}
                            required
                        />

                        <Input
                            label={t('email')}
                            type="email"
                            value={profileForm.data.email}
                            onChange={(e) => profileForm.setData('email', e.target.value)}
                            error={profileForm.errors.email}
                            required
                        />

                        <Input
                            label={t('phone')}
                            type="tel"
                            value={profileForm.data.phone}
                            onChange={(e) => profileForm.setData('phone', e.target.value)}
                            error={profileForm.errors.phone}
                        />

                        <div>
                            <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                                {t('language')}
                            </label>
                            <select
                                value={profileForm.data.locale}
                                onChange={(e) => profileForm.setData('locale', e.target.value as 'ar' | 'fr' | 'en')}
                                className="w-full px-4 py-2.5 rounded-xl text-sm bg-white dark:bg-[#141A2E] text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 focus:outline-none focus:ring-2 focus:ring-[#6C5CE7]"
                            >
                                <option value="ar">{t('arabic')}</option>
                                <option value="fr">{t('french')}</option>
                                <option value="en">{t('english')}</option>
                            </select>
                        </div>

                        <Button type="submit" variant="primary" isLoading={profileForm.processing}>
                            {t('save')}
                        </Button>
                    </form>
                </Card>

                {/* Password Change */}
                <Card className="space-y-6">
                    <div className="flex items-center gap-3 border-b border-slate-100 dark:border-slate-800 pb-4">
                        <div className="w-10 h-10 rounded-xl bg-amber-500/15 text-amber-600 flex items-center justify-center">
                            <Lock className="w-5 h-5" />
                        </div>
                        <div>
                            <h3 className="font-bold text-base text-slate-900 dark:text-white">
                                {t('security')}
                            </h3>
                            <p className="text-xs text-slate-500">Ensure your account is using a long, random password</p>
                        </div>
                    </div>

                    <form onSubmit={submitPassword} className="space-y-4 max-w-xl">
                        <Input
                            label={t('current_password')}
                            type="password"
                            value={passwordForm.data.current_password}
                            onChange={(e) => passwordForm.setData('current_password', e.target.value)}
                            error={passwordForm.errors.current_password}
                            required
                        />

                        <Input
                            label={t('new_password')}
                            type="password"
                            value={passwordForm.data.password}
                            onChange={(e) => passwordForm.setData('password', e.target.value)}
                            error={passwordForm.errors.password}
                            required
                        />

                        <Input
                            label={t('password_confirmation')}
                            type="password"
                            value={passwordForm.data.password_confirmation}
                            onChange={(e) => passwordForm.setData('password_confirmation', e.target.value)}
                            error={passwordForm.errors.password_confirmation}
                            required
                        />

                        <Button type="submit" variant="primary" isLoading={passwordForm.processing}>
                            {t('save')}
                        </Button>
                    </form>
                </Card>
            </div>
        </AppLayout>
    );
}
