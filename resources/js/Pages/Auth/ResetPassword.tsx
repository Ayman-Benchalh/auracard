import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import { Eye, EyeOff, Lock, Mail } from 'lucide-react';
import { GuestLayout } from '@/Layouts/GuestLayout';
import { Input } from '@/Components/UI/Input';
import { Button } from '@/Components/UI/Button';
import { useTranslation } from '@/Hooks/useTranslation';

export default function ResetPassword({ token, email }: { token: string; email: string }) {
    const { t } = useTranslation();
    const [showPassword, setShowPassword] = useState(false);

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
                    autoComplete="new-password"
                    autoFocus
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

                <Button
                    type="submit"
                    variant="primary"
                    className="w-full min-h-[44px] text-sm font-semibold tracking-wide"
                    isLoading={processing}
                >
                    {t('reset_password')}
                </Button>
            </form>
        </GuestLayout>
    );
}
