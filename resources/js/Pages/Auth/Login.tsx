import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { Eye, EyeOff, Lock, Mail } from 'lucide-react';
import { GuestLayout } from '@/Layouts/GuestLayout';
import { Input } from '@/Components/UI/Input';
import { Button } from '@/Components/UI/Button';
import { SocialLoginButtons } from '@/Components/Auth/SocialLoginButtons';
import { useTranslation } from '@/Hooks/useTranslation';

export default function Login({ status }: { status?: string }) {
    const { t } = useTranslation();
    const [showPassword, setShowPassword] = useState(false);

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
        <GuestLayout title={t('login')} subtitle={t('sign_in_subtitle')}>
            <Head title={t('login')} />

            {status && (
                <div className="mb-5 text-sm font-medium text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 p-3.5 rounded-xl border border-emerald-200 dark:border-emerald-800">
                    {status}
                </div>
            )}

            {/* Social Authentication */}
            <SocialLoginButtons mode="login" className="mb-4" />

            {/* Standard Email/Password Form */}
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

                <Input
                    label={t('password')}
                    type={showPassword ? 'text' : 'password'}
                    name="password"
                    value={data.password}
                    autoComplete="current-password"
                    placeholder="••••••••"
                    startSlot={<Lock className="w-4 h-4" />}
                    endSlot={
                        <button
                            type="button"
                            onClick={() => setShowPassword(!showPassword)}
                            className="p-1 rounded-md text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors focus:outline-none"
                            tabIndex={-1}
                            aria-label={showPassword ? 'Hide password' : 'Show password'}
                        >
                            {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                        </button>
                    }
                    onChange={(e) => setData('password', e.target.value)}
                    error={errors.password}
                    required
                />

                <div className="flex items-center justify-between text-sm pt-1">
                    <label className="flex items-center gap-2 cursor-pointer select-none">
                        <input
                            type="checkbox"
                            name="remember"
                            checked={data.remember}
                            onChange={(e) => setData('remember', e.target.checked)}
                            className="w-4 h-4 rounded border-slate-300 text-[#6C5CE7] focus:ring-[#6C5CE7] dark:bg-slate-800 dark:border-slate-700"
                        />
                        <span className="text-xs sm:text-sm text-slate-600 dark:text-slate-400">
                            {t('remember_me')}
                        </span>
                    </label>

                    <Link
                        href="/forgot-password"
                        className="text-xs font-semibold text-[#6C5CE7] hover:text-[#5B4BD6] dark:text-[#818CF8] hover:underline"
                    >
                        {t('forgot_password')}
                    </Link>
                </div>

                <Button
                    type="submit"
                    variant="primary"
                    className="w-full min-h-[44px] text-sm font-semibold tracking-wide mt-2"
                    isLoading={processing}
                >
                    {t('login')}
                </Button>

                <div className="pt-4 text-center text-xs sm:text-sm text-slate-500 dark:text-slate-400 border-t border-slate-200 dark:border-slate-800/80">
                    {t('dont_have_account')}{' '}
                    <Link
                        href="/register"
                        className="font-bold text-[#6C5CE7] dark:text-[#818CF8] hover:underline"
                    >
                        {t('register')}
                    </Link>
                </div>
            </form>
        </GuestLayout>
    );
}
