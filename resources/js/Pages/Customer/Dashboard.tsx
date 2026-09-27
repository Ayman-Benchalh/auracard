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
    ExternalLink,
    Sliders,
    Eye,
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
    profileSummary: {
        id: string;
        slug: string;
        display_name: string;
        status: string;
        current_version: number;
        published_at: string | null;
        public_url: string;
    } | null;
    subscriptionSummary: any | null;
    editCreditsBalance: number;
}

export default function Dashboard({
    account,
    cardSummary,
    profileSummary,
    editCreditsBalance,
}: DashboardProps) {
    const { t, isRtl } = useTranslation();

    const formattedDate = new Date(account.created_at).toLocaleDateString(
        account.locale === 'ar' ? 'ar-MA' : account.locale === 'fr' ? 'fr-FR' : 'en-US',
        { year: 'numeric', month: 'long', day: 'numeric' }
    );

    return (
        <AppLayout title={t('dashboard')}>
            <Head title={t('dashboard')} />

            <div className="space-y-8">
                {/* Welcome Executive Banner */}
                <div className="relative overflow-hidden rounded-3xl p-6 sm:p-8 bg-gradient-to-r from-[#6C5CE7] to-[#14B8A6] text-white shadow-xl shadow-[#6C5CE7]/15">
                    <div className="relative z-10 max-w-2xl space-y-2">
                        <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 text-white text-xs font-bold backdrop-blur-xs">
                            <Sparkles className="w-3.5 h-3.5" />
                            <span>AURA CARD Executive Portal</span>
                        </div>
                        <h2 className="text-2xl sm:text-3xl font-extrabold tracking-tight">
                            {t('welcome')}, {account.name}
                        </h2>
                        <p className="text-white/80 text-xs sm:text-sm">
                            Gérez vos cartes physiques NFC, vos profils digitaux interactifs et vos paramètres de sécurité.
                        </p>
                    </div>

                    <div className="absolute -end-10 -bottom-10 opacity-15 pointer-events-none">
                        <CreditCard className="w-64 h-64" />
                    </div>
                </div>

                {/* Key Metrics Grid */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    {/* Account Status */}
                    <Card className="p-5 space-y-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#11182A]">
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
                                className="text-xs px-2.5 py-1"
                            >
                                {t(account.status)}
                            </Badge>
                        </div>
                        <p className="text-xs text-slate-500 dark:text-slate-400">
                            Membre depuis le {formattedDate}
                        </p>
                    </Card>

                    {/* Email Verification */}
                    <Card className="p-5 space-y-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#11182A]">
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
                                <Badge variant="success" className="text-xs px-2.5 py-1">
                                    {t('verified')}
                                </Badge>
                            ) : (
                                <div className="space-y-1.5">
                                    <Badge variant="warning" className="text-xs px-2.5 py-1">
                                        {t('unverified')}
                                    </Badge>
                                    <Link
                                        href="/verify-email"
                                        className="block text-xs font-bold text-[#6C5CE7] hover:underline"
                                    >
                                        Vérifier mon e-mail &rarr;
                                    </Link>
                                </div>
                            )}
                        </div>
                        <p className="text-xs text-slate-500 dark:text-slate-400 truncate">{account.email}</p>
                    </Card>

                    {/* Edit Credits Balance */}
                    <Card className="p-5 space-y-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#11182A]">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-bold uppercase tracking-wider text-slate-400">
                                {t('free_credits')}
                            </span>
                            <Sparkles className="w-4 h-4 text-amber-500" />
                        </div>
                        <div className="text-2xl font-black text-slate-900 dark:text-white">
                            {editCreditsBalance}{' '}
                            <span className="text-xs font-semibold text-slate-400 uppercase">Crédits</span>
                        </div>
                        <p className="text-xs text-slate-500 dark:text-slate-400">
                            Publication de révisions de profils (brouillons illimités)
                        </p>
                    </Card>
                </div>

                {/* Modules Grid */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {/* NFC Card Section */}
                    <Card className="p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#11182A] space-y-4">
                        <div className="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                            <div className="flex items-center gap-3">
                                <div className="w-10 h-10 rounded-xl bg-[#6C5CE7]/10 text-[#6C5CE7] flex items-center justify-center">
                                    <Smartphone className="w-5 h-5" />
                                </div>
                                <div>
                                    <h3 className="font-bold text-base text-slate-900 dark:text-white">
                                        {t('my_card')}
                                    </h3>
                                    <p className="text-xs text-slate-500">Matériel physique NFC appairé</p>
                                </div>
                            </div>
                            <Badge variant={cardSummary ? 'success' : 'neutral'}>
                                {cardSummary ? `${cardSummary.total_cards} Active${cardSummary.total_cards > 1 ? 's' : ''}` : 'Non liée'}
                            </Badge>
                        </div>

                        {cardSummary ? (
                            <div className="space-y-4 bg-slate-50 dark:bg-slate-900/60 rounded-xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800">
                                <div className="flex items-center justify-between">
                                    <div className="space-y-0.5">
                                        <span className="text-[10px] text-slate-400 font-mono block uppercase">
                                            Numéro de Série
                                        </span>
                                        <span className="text-sm font-mono font-bold text-slate-900 dark:text-white">
                                            {cardSummary.serial_number}
                                        </span>
                                    </div>
                                    <Badge variant={cardSummary.status === 'active' ? 'success' : 'warning'}>
                                        {cardSummary.status_label}
                                    </Badge>
                                </div>

                                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-3 border-t border-slate-200 dark:border-slate-800 text-xs">
                                    <span className="text-slate-500 capitalize">
                                        Type : {cardSummary.card_type.replace('_', ' ')}
                                    </span>
                                    <Link
                                        href="/cards"
                                        className="text-[#6C5CE7] dark:text-[#818CF8] hover:underline font-bold inline-flex items-center gap-1"
                                    >
                                        <span>Gérer mes cartes NFC &rarr;</span>
                                    </Link>
                                </div>
                            </div>
                        ) : (
                            <div className="py-6 text-center space-y-3 bg-slate-50 dark:bg-slate-900/60 rounded-xl p-6 border border-dashed border-slate-200 dark:border-slate-800">
                                <CreditCard className="w-10 h-10 text-slate-400 mx-auto" />
                                <p className="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Aucune carte NFC activée
                                </p>
                                <p className="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                                    Activez votre carte reçue grâce à son code d’activation confidentiel.
                                </p>
                                <div className="pt-2">
                                    <Link href="/cards/claim">
                                        <Button size="sm" className="inline-flex items-center gap-1.5 min-h-[40px]">
                                            <Sparkles className="w-3.5 h-3.5" />
                                            <span>Activer ma carte</span>
                                        </Button>
                                    </Link>
                                </div>
                            </div>
                        )}
                    </Card>

                    {/* Digital Profile Section */}
                    <Card className="p-6 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#11182A] space-y-4">
                        <div className="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                            <div className="flex items-center gap-3">
                                <div className="w-10 h-10 rounded-xl bg-[#14B8A6]/10 text-[#14B8A6] flex items-center justify-center">
                                    <User className="w-5 h-5" />
                                </div>
                                <div>
                                    <h3 className="font-bold text-base text-slate-900 dark:text-white">
                                        {t('my_profile')}
                                    </h3>
                                    <p className="text-xs text-slate-500">Profil digital interactif</p>
                                </div>
                            </div>
                            <Badge variant={profileSummary ? (profileSummary.status === 'published' ? 'success' : 'warning') : 'neutral'}>
                                {profileSummary ? profileSummary.status : 'Non créé'}
                            </Badge>
                        </div>

                        {profileSummary ? (
                            <div className="space-y-4 bg-slate-50 dark:bg-slate-900/60 rounded-xl p-4 sm:p-5 border border-slate-200 dark:border-slate-800">
                                <div className="flex items-center justify-between">
                                    <div>
                                        <h4 className="text-sm font-bold text-slate-900 dark:text-white">
                                            {profileSummary.display_name}
                                        </h4>
                                        <p className="text-xs font-mono text-slate-400">
                                            /p/{profileSummary.slug}
                                        </p>
                                    </div>
                                    <span className="text-[10px] font-mono px-2 py-0.5 rounded bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                        v{profileSummary.current_version}
                                    </span>
                                </div>

                                <div className="flex flex-wrap items-center gap-2 pt-3 border-t border-slate-200 dark:border-slate-800">
                                    <Link href={`/profile/${profileSummary.id}/builder`} className="flex-1 min-w-[120px]">
                                        <Button size="sm" variant="primary" className="w-full min-h-[40px] text-xs font-semibold">
                                            <Sliders className="w-3.5 h-3.5 me-1.5" />
                                            <span>Éditeur de Profil</span>
                                        </Button>
                                    </Link>
                                    <a
                                        href={profileSummary.public_url}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="flex-1 min-w-[120px]"
                                    >
                                        <Button size="sm" variant="outline" className="w-full min-h-[40px] text-xs font-semibold">
                                            <ExternalLink className="w-3.5 h-3.5 me-1.5" />
                                            <span>Voir Profil Public</span>
                                        </Button>
                                    </a>
                                </div>
                            </div>
                        ) : (
                            <div className="py-6 text-center space-y-3 bg-slate-50 dark:bg-slate-900/60 rounded-xl p-6 border border-dashed border-slate-200 dark:border-slate-800">
                                <Sparkles className="w-10 h-10 text-slate-400 mx-auto" />
                                <p className="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Aucun profil digital configuré
                                </p>
                                <p className="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
                                    Créez votre page professionnelle en 2 minutes avec vos blocs de contact et liens.
                                </p>
                                <div className="pt-2">
                                    <Link href="/profile/create">
                                        <Button size="sm" variant="secondary" className="inline-flex items-center gap-1.5 min-h-[40px]">
                                            <User className="w-3.5 h-3.5" />
                                            <span>Créer mon profil</span>
                                        </Button>
                                    </Link>
                                </div>
                            </div>
                        )}
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
