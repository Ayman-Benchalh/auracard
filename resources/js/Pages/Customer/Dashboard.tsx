import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { AppLayout } from '@/Layouts/AppLayout';
import { Card } from '@/Components/UI/Card';
import { Badge } from '@/Components/UI/Badge';
import { Button } from '@/Components/UI/Button';
import { useTranslation } from '@/Hooks/useTranslation';
import {
    CheckCircle2,
    AlertCircle,
    Smartphone,
    CreditCard,
    User,
    Sparkles,
    ArrowRight,
    Lock,
    Shield,
    Calendar,
} from 'lucide-react';

interface DashboardProps {
    account: {
        name: string;
        email: string;
        phone: string | null;
        status: 'active' | 'suspended' | 'disabled';
        is_verified: boolean;
        locale: string;
        created_at: string;
    };
    cardSummary: {
        id: string;
        serial_number: string;
        status: string;
        status_label: string;
        status_color: string;
        card_type: string;
        claimed_at: string | null;
        has_entitlement: boolean;
        total_cards: number;
    } | null;
    profileSummary: null;
    subscriptionSummary: null;
    editCreditsBalance: number;
}

export default function Dashboard({ account, cardSummary, editCreditsBalance }: DashboardProps) {
    const { t, isRtl } = useTranslation();

    const formattedDate = new Date(account.created_at).toLocaleDateString(
        account.locale === 'ar' ? 'ar-MA' : account.locale === 'fr' ? 'fr-FR' : 'en-US',
        { year: 'numeric', month: 'long', day: 'numeric' }
    );

    return (
        <AppLayout title={t('dashboard')}>
            <Head title={t('dashboard')} />

            <div className="space-y-8">
                {/* Welcome Banner */}
                <div className="relative overflow-hidden rounded-3xl p-8 bg-gradient-to-r from-[#6C5CE7] to-[#14B8A6] text-white shadow-xl shadow-[#6C5CE7]/20">
                    <div className="relative z-10 max-w-2xl space-y-2">
                        <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 text-white text-xs font-bold backdrop-blur-xs">
                            <Sparkles className="w-3.5 h-3.5" />
                            <span>AURA CARD Customer Portal</span>
                        </div>
                        <h2 className="text-2xl sm:text-3xl font-extrabold tracking-tight">
                            {t('welcome')}, {account.name}
                        </h2>
                        <p className="text-white/80 text-sm">
                            Manage your NFC cards, digital identity profiles, and view your account security.
                        </p>
                    </div>

                    <div className="absolute -end-10 -bottom-10 opacity-15 pointer-events-none">
                        <CreditCard className="w-64 h-64" />
                    </div>
                </div>

                {/* Account Status & Foundation Metrics Grid */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {/* Account Status Card */}
                    <Card className="space-y-3">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-bold uppercase tracking-wider text-slate-400">
                                {t('account_status')}
                            </span>
                            <Shield className="w-4 h-4 text-slate-400" />
                        </div>
                        <div className="flex items-center gap-3">
                            <Badge
                                variant={
                                    account.status === 'active'
                                        ? 'success'
                                        : account.status === 'suspended'
                                        ? 'warning'
                                        : 'danger'
                                }
                                className="text-sm px-3 py-1"
                            >
                                {t(account.status)}
                            </Badge>
                        </div>
                        <p className="text-xs text-slate-500 dark:text-slate-400">
                            Member since {formattedDate}
                        </p>
                    </Card>

                    {/* Email Verification Card */}
                    <Card className="space-y-3">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-bold uppercase tracking-wider text-slate-400">
                                {t('verify_email')}
                            </span>
                            {account.is_verified ? (
                                <CheckCircle2 className="w-4 h-4 text-emerald-500" />
                            ) : (
                                <AlertCircle className="w-4 h-4 text-amber-500" />
                            )}
                        </div>
                        <div>
                            {account.is_verified ? (
                                <Badge variant="success" className="text-sm px-3 py-1">
                                    {t('verified')}
                                </Badge>
                            ) : (
                                <div className="space-y-2">
                                    <Badge variant="warning" className="text-sm px-3 py-1">
                                        {t('unverified')}
                                    </Badge>
                                    <Link href="/verify-email" className="block text-xs font-bold text-[#6C5CE7] hover:underline">
                                        Verify your email now &rarr;
                                    </Link>
                                </div>
                            )}
                        </div>
                        <p className="text-xs text-slate-500 dark:text-slate-400 truncate">{account.email}</p>
                    </Card>

                    {/* Edit Credits Balance Card */}
                    <Card className="space-y-3">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-bold uppercase tracking-wider text-slate-400">
                                {t('free_credits')}
                            </span>
                            <Sparkles className="w-4 h-4 text-amber-500" />
                        </div>
                        <div className="text-2xl font-black text-slate-900 dark:text-white">
                            {editCreditsBalance}{' '}
                            <span className="text-xs font-semibold text-slate-400 uppercase">Credits</span>
                        </div>
                        <p className="text-xs text-slate-500 dark:text-slate-400">
                            Granted automatically when claiming an eligible NFC card
                        </p>
                    </Card>
                </div>

                {/* Modules Grid */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* NFC Card Section (Phase 2 Active) */}
                    <Card className="space-y-4">
                        <div className="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                            <div className="flex items-center gap-3">
                                <div className="w-10 h-10 rounded-xl bg-[#6C5CE7]/15 text-[#6C5CE7] flex items-center justify-center">
                                    <Smartphone className="w-5 h-5" />
                                </div>
                                <div>
                                    <h3 className="font-bold text-base text-slate-900 dark:text-white">
                                        {t('my_card')}
                                    </h3>
                                    <p className="text-xs text-slate-500">Physical NFC Hardware Connection</p>
                                </div>
                            </div>
                            <Badge variant={cardSummary ? 'success' : 'neutral'}>
                                {cardSummary ? `${cardSummary.total_cards} Linked` : 'Phase 2 Live'}
                            </Badge>
                        </div>

                        {cardSummary ? (
                            <div className="space-y-4 bg-slate-50 dark:bg-[#1E2642]/50 rounded-2xl p-5 border border-slate-200 dark:border-slate-700/60">
                                <div className="flex items-center justify-between">
                                    <div className="space-y-0.5">
                                        <span className="text-xs text-slate-400 font-mono block">Serial Number</span>
                                        <span className="text-sm font-mono font-bold text-slate-900 dark:text-white">
                                            {cardSummary.serial_number}
                                        </span>
                                    </div>
                                    <Badge variant={cardSummary.status === 'active' ? 'success' : 'warning'}>
                                        {cardSummary.status_label}
                                    </Badge>
                                </div>

                                <div className="flex items-center justify-between pt-2 border-t border-slate-200/60 dark:border-slate-700/60 text-xs">
                                    <span className="text-slate-500 capitalize">
                                        Type: {cardSummary.card_type.replace('_', ' ')}
                                    </span>
                                    <Link
                                        href="/cards"
                                        className="text-[#6C5CE7] hover:underline font-bold inline-flex items-center gap-1"
                                    >
                                        <span>Manage NFC Cards &rarr;</span>
                                    </Link>
                                </div>
                            </div>
                        ) : (
                            <div className="py-6 text-center space-y-3 bg-slate-50 dark:bg-[#1E2642]/50 rounded-2xl p-6 border border-dashed border-slate-200 dark:border-slate-700">
                                <CreditCard className="w-10 h-10 text-slate-400 mx-auto" />
                                <p className="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    No NFC card claimed yet
                                </p>
                                <p className="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                                    Once you receive your physical AURA CARD, enter your activation code to link it to your account.
                                </p>
                                <div className="pt-2">
                                    <Link href="/cards/claim">
                                        <Button size="sm" className="inline-flex items-center gap-1.5">
                                            <Sparkles className="w-3.5 h-3.5" />
                                            <span>Activate Your Card</span>
                                        </Button>
                                    </Link>
                                </div>
                            </div>
                        )}
                    </Card>

                    {/* Digital Profile Section (Phase 3) */}
                    <Card className="space-y-4">
                        <div className="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                            <div className="flex items-center gap-3">
                                <div className="w-10 h-10 rounded-xl bg-[#14B8A6]/15 text-[#14B8A6] flex items-center justify-center">
                                    <User className="w-5 h-5" />
                                </div>
                                <div>
                                    <h3 className="font-bold text-base text-slate-900 dark:text-white">
                                        {t('my_profile')}
                                    </h3>
                                    <p className="text-xs text-slate-500">Digital Identity & Modular Blocks</p>
                                </div>
                            </div>
                            <Badge variant="neutral">Phase 3 Engine</Badge>
                        </div>

                        <div className="py-6 text-center space-y-3 bg-slate-50 dark:bg-[#1E2642]/50 rounded-2xl p-6 border border-dashed border-slate-200 dark:border-slate-700">
                            <Sparkles className="w-10 h-10 text-slate-400 mx-auto" />
                            <p className="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                Profile Builder Ready for Activation
                            </p>
                            <p className="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                                The modular 12-block builder will be configured during Phase 3 with instant vCard and QR code export.
                            </p>
                        </div>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
