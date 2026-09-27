import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { Eye, EyeOff, Lock, Mail, Phone, User } from 'lucide-react';
import { GuestLayout } from '@/Layouts/GuestLayout';
import { Input } from '@/Components/UI/Input';
import { Button } from '@/Components/UI/Button';
import { SocialLoginButtons } from '@/Components/Auth/SocialLoginButtons';
import { useTranslation } from '@/Hooks/useTranslation';

export default function Register() {
    const { t } = useTranslation();
    const [showPassword, setShowPassword] = useState(false);

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
        <GuestLayout title={t('register')} subtitle={t('create_account_subtitle')}>
            <Head title={t('register')} />

            {/* Social Authentication */}
            <SocialLoginButtons mode="register" className="mb-4" />

            <form onSubmit={submit} className="space-y-4">
                <Input
                    label={t('name')}
                    type="text"
                    name="name"
                    value={data.name}
                    autoComplete="name"
                    autoFocus
                    placeholder="ex. Yassine Bennani"
                    startSlot={<User className="w-4 h-4" />}
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
                    placeholder="nom@exemple.ma"
                    startSlot={<Mail className="w-4 h-4" />}
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
                    startSlot={<Phone className="w-4 h-4" />}
                    onChange={(e) => setData('phone', e.target.value)}
                    error={errors.phone}
                />

                <Input
                    label={t('password')}
                    type={showPassword ? 'text' : 'password'}
                    name="password"
                    value={data.password}
                    autoComplete="new-password"
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

                <Input
                    label={t('password_confirmation')}
                    type={showPassword ? 'text' : 'password'}
                    name="password_confirmation"
                    value={data.password_confirmation}
                    autoComplete="new-password"
                    placeholder="••••••••"
                    startSlot={<Lock className="w-4 h-4" />}
                    onChange={(e) => setData('password_confirmation', e.target.value)}
                    error={errors.password_confirmation}
                    required
                />

                <p className="text-[11px] text-slate-500 dark:text-slate-400 leading-normal">
                    {t('terms_and_privacy_notice')}
                </p>

                <Button
                    type="submit"
                    variant="primary"
                    className="w-full min-h-[44px] text-sm font-semibold tracking-wide mt-2"
                    isLoading={processing}
                >
                    {t('register')}
                </Button>

                <div className="pt-4 text-center text-xs sm:text-sm text-slate-500 dark:text-slate-400 border-t border-slate-200 dark:border-slate-800/80">
                    {t('already_registered')}{' '}
                    <Link
                        href="/login"
                        className="font-bold text-[#6C5CE7] dark:text-[#818CF8] hover:underline"
                    >
                        {t('login')}
                    </Link>
                </div>
            </form>
        </GuestLayout>
    );
}
