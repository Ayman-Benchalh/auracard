import React, { useState } from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import { AppLayout } from '@/Layouts/AppLayout';
import { Card } from '@/Components/UI/Card';
import { Button } from '@/Components/UI/Button';
import { Badge } from '@/Components/UI/Badge';
import {
    Check,
    Sparkles,
    Shield,
    Zap,
    CreditCard,
    ArrowRight,
    HelpCircle,
    Smartphone,
    Layers,
    Palette,
    Building2,
    CheckCircle2,
    Lock,
    ShieldCheck,
} from 'lucide-react';
import { PageProps } from '@/types';

interface CommercialPackageItem {
    id: string;
    key: string;
    name: Record<string, string> | string;
    description: Record<string, string> | string;
    price: number;
    currency: string;
    duration_months: number;
    physical_cards_included: number;
    profiles_included: number;
    edit_credits_per_profile: number;
    allows_customer_owned_card: boolean;
    requires_customer_owned_card: boolean;
    is_featured: boolean;
    features_list?: Record<string, string[]> | null;
}

interface CardDesignItem {
    id: string;
    key: string;
    name: Record<string, string> | string;
    description: Record<string, string> | string;
    material: string;
    color: string;
    preview_image?: string;
}

interface ActivePurchaseItem {
    id: string;
    package_key?: string;
    package_name?: Record<string, string> | string;
    ends_at?: string;
}

interface FeatureItem {
    key: string;
    name: string;
    description: string;
    value: string;
}

interface PlanItem {
    id: string;
    key: string;
    name: string;
    description: string;
    price: number;
    currency: string;
    billing_period: string;
    is_recommended: boolean;
    features: FeatureItem[];
}

interface ActiveSubscription {
    id: string;
    plan_key?: string;
    plan_name?: string;
    price: number;
    currency: string;
    starts_at?: string;
    ends_at?: string;
    status: string;
}

interface Props {
    packages?: CommercialPackageItem[];
    cardDesigns?: CardDesignItem[];
    activePurchases?: ActivePurchaseItem[];
    plans?: PlanItem[];
    activeSubscription?: ActiveSubscription | null;
    editCreditBalance: number;
}

export default function Pricing({
    packages = [],
    cardDesigns = [],
    activePurchases = [],
    plans = [],
    activeSubscription,
    editCreditBalance,
}: Props) {
    const { auth, locale = 'fr' } = usePage<PageProps>().props;
    const isAuthenticated = !!auth?.user;

    const getLocalized = (field: any, defaultStr: string = '') => {
        if (!field) return defaultStr;
        if (typeof field === 'string') return field;
        return field[locale] || field['fr'] || field['en'] || defaultStr;
    };

    const getFeaturesList = (pkg: CommercialPackageItem): string[] => {
        if (!pkg.features_list) return [];
        return pkg.features_list[locale] || pkg.features_list['fr'] || pkg.features_list['en'] || [];
    };

    const getCardStyle = (designKey: string) => {
        switch (designKey) {
            case 'metal_gold':
                return 'bg-gradient-to-tr from-[#92400E] via-[#D97706] to-[#FDE68A] text-slate-900 border-[#B45309]';
            case 'bamboo_wood':
                return 'bg-gradient-to-tr from-[#451A03] via-[#78350F] to-[#B45309] text-amber-50 border-[#92400E]';
            case 'aura_gradient':
                return 'bg-gradient-to-tr from-[#4F46E5] via-[#7C3AED] to-[#06B6D4] text-white border-[#818CF8]';
            case 'matte_black':
            default:
                return 'bg-gradient-to-tr from-[#0F172A] via-[#1E293B] to-[#334155] text-slate-100 border-slate-700';
        }
    };

    const content = (
        <div className="max-w-6xl mx-auto space-y-20 py-8 px-4 sm:px-6">
            {/* Header */}
            <div className="text-center max-w-3xl mx-auto space-y-4">
                <Badge variant="primary" className="px-4 py-1.5 text-xs uppercase tracking-wider font-bold">
                    Tarifs Maroc • Hardware NFC & SaaS Professionnel
                </Badge>
                <h1 className="text-3xl sm:text-5xl font-black text-slate-900 dark:text-white tracking-tight">
                    Cartes NFC Connectées & Profils Digitaux
                </h1>
                <p className="text-base sm:text-lg text-slate-600 dark:text-slate-400">
                    Finition gravure laser, puces NFC sécurisées et plateforme SaaS pour indépendants, dirigeants et entreprises au Maroc.
                </p>

                {isAuthenticated && (
                    <div className="flex flex-wrap items-center justify-center gap-3 pt-2">
                        <div className="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs text-slate-700 dark:text-slate-300">
                            <CreditCard className="w-4 h-4 text-[#6C5CE7]" />
                            <span>Solde de crédits de modification : <strong className="text-slate-900 dark:text-white font-bold">{editCreditBalance}</strong></span>
                        </div>
                        <Link href="/orders" className="text-xs font-semibold text-[#6C5CE7] hover:underline">
                            Voir mes commandes et forfaits →
                        </Link>
                    </div>
                )}
            </div>

            {/* Commercial Packages Grid */}
            {packages && packages.length > 0 ? (
                <div className="space-y-6">
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-stretch">
                        {packages.map((pkg) => {
                            const isFeatured = pkg.is_featured;
                            const features = getFeaturesList(pkg);

                            return (
                                <div
                                    key={pkg.id}
                                    className={`relative rounded-3xl p-7 flex flex-col justify-between transition-all duration-300 ${
                                        isFeatured
                                            ? 'bg-gradient-to-b from-[#6C5CE7]/10 via-white to-white dark:from-[#6C5CE7]/20 dark:via-[#141A2E] dark:to-[#141A2E] border-2 border-[#6C5CE7] shadow-xl shadow-[#6C5CE7]/20 lg:-translate-y-2 z-10'
                                            : 'bg-white dark:bg-[#141A2E] border border-slate-200 dark:border-slate-800 shadow-sm hover:border-slate-300 dark:hover:border-slate-700'
                                    }`}
                                >
                                    {isFeatured && (
                                        <div className="absolute -top-3.5 left-1/2 -translate-x-1/2">
                                            <span className="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-bold bg-[#6C5CE7] text-white shadow-md shadow-[#6C5CE7]/40">
                                                <Sparkles className="w-3.5 h-3.5" /> Recommandé
                                            </span>
                                        </div>
                                    )}

                                    <div>
                                        <div className="flex items-center justify-between">
                                            <h3 className="text-lg font-black text-slate-900 dark:text-white">
                                                {getLocalized(pkg.name)}
                                            </h3>
                                        </div>

                                        <p className="mt-2 text-xs text-slate-500 dark:text-slate-400 min-h-[36px]">
                                            {getLocalized(pkg.description)}
                                        </p>

                                        {/* Price */}
                                        <div className="mt-5 flex items-baseline gap-1.5">
                                            <span className="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight">
                                                {pkg.price.toFixed(0)}
                                            </span>
                                            <span className="text-base font-bold text-slate-600 dark:text-slate-400">
                                                {pkg.currency}
                                            </span>
                                            <span className="text-xs text-slate-500">
                                                / {pkg.duration_months} mois
                                            </span>
                                        </div>

                                        {/* Inclusions Chips */}
                                        <div className="mt-4 flex flex-wrap gap-2 text-[11px] font-semibold">
                                            <span className="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                                {pkg.physical_cards_included} Carte{pkg.physical_cards_included > 1 ? 's' : ''} NFC
                                            </span>
                                            <span className="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                                {pkg.profiles_included} Profil{pkg.profiles_included > 1 ? 's' : ''} Pro
                                            </span>
                                            <span className="px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-300">
                                                {pkg.edit_credits_per_profile} Crédits/profil
                                            </span>
                                        </div>

                                        {/* Features List */}
                                        <div className="mt-6 border-t border-slate-100 dark:border-slate-800/80 pt-5 space-y-2.5">
                                            <p className="text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                                Avantages inclus :
                                            </p>
                                            <ul className="space-y-2">
                                                {features.map((feature, idx) => (
                                                    <li key={idx} className="flex items-start gap-2.5 text-xs text-slate-700 dark:text-slate-300">
                                                        <div className="mt-0.5 w-4 h-4 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                                            <Check className="w-3 h-3" />
                                                        </div>
                                                        <span>{feature}</span>
                                                    </li>
                                                ))}
                                            </ul>
                                        </div>
                                    </div>

                                    {/* Action Button */}
                                    <div className="mt-8 pt-5 border-t border-slate-100 dark:border-slate-800/80">
                                        <Link href={`/checkout/package/${pkg.id}`} className="block">
                                            <Button
                                                variant={isFeatured ? 'primary' : 'outline'}
                                                className={`w-full py-3 text-xs font-bold flex items-center justify-center gap-1.5 ${
                                                    isFeatured ? 'shadow-lg shadow-[#6C5CE7]/30' : ''
                                                }`}
                                            >
                                                <span>Choisir ce pack</span>
                                                <ArrowRight className="w-3.5 h-3.5" />
                                            </Button>
                                        </Link>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div>
            ) : null}

            {/* Physical Card Designs Showcase */}
            {cardDesigns && cardDesigns.length > 0 && (
                <div className="space-y-8">
                    <div className="text-center max-w-2xl mx-auto space-y-2">
                        <Badge variant="neutral" className="text-xs uppercase font-bold">
                            Matériaux de Haute Finition
                        </Badge>
                        <h2 className="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white">
                            Nos Finitions de Cartes Physiques
                        </h2>
                        <p className="text-xs sm:text-sm text-slate-500">
                            Chaque carte est gravée au laser au Maroc et équipée d'une puce NFC NTAG213/215 garantie à vie.
                        </p>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        {cardDesigns.map((design) => (
                            <div
                                key={design.key}
                                className="p-5 rounded-3xl bg-white dark:bg-[#141A2E] border border-slate-200 dark:border-slate-800 shadow-sm space-y-4 flex flex-col justify-between"
                            >
                                <div
                                    className={`aspect-[1.586/1] w-full rounded-2xl p-4 flex flex-col justify-between border shadow-lg relative overflow-hidden ${getCardStyle(
                                        design.key
                                    )}`}
                                >
                                    <div className="flex items-center justify-between text-[11px] font-black uppercase tracking-wider">
                                        <span>AURA CARD</span>
                                        <CreditCard className="w-3.5 h-3.5 opacity-80" />
                                    </div>
                                    <div className="text-center">
                                        <div className="font-extrabold text-xs">
                                            {getLocalized(design.name)}
                                        </div>
                                    </div>
                                    <div className="flex items-center justify-between text-[9px] font-mono opacity-80">
                                        <span>NFC ENCRYPTED</span>
                                        <span>MAROC</span>
                                    </div>
                                </div>

                                <div className="space-y-1">
                                    <div className="flex items-center justify-between">
                                        <h4 className="font-bold text-sm text-slate-900 dark:text-white">
                                            {getLocalized(design.name)}
                                        </h4>
                                        <span className="text-[10px] uppercase font-semibold px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500">
                                            {design.material.replace('_', ' ')}
                                        </span>
                                    </div>
                                    <p className="text-xs text-slate-500 dark:text-slate-400">
                                        {getLocalized(design.description)}
                                    </p>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            )}

            {/* Moroccan Payment Rail Confidence Bar */}
            <Card className="p-8 rounded-3xl border border-slate-200 dark:border-slate-800 bg-gradient-to-r from-slate-50 via-white to-slate-50 dark:from-[#101524] dark:via-[#141A2E] dark:to-[#101524] shadow-sm">
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6 text-center md:text-left items-center">
                    <div className="flex items-center gap-4 justify-center md:justify-start">
                        <div className="w-12 h-12 rounded-2xl bg-[#6C5CE7]/10 text-[#6C5CE7] flex items-center justify-center shrink-0">
                            <Building2 className="w-6 h-6" />
                        </div>
                        <div>
                            <div className="font-extrabold text-sm text-slate-900 dark:text-white">
                                Virement Bancaire National
                            </div>
                            <div className="text-xs text-slate-500">
                                Réseau bancaire national (RIB 24 chiffres)
                            </div>
                        </div>
                    </div>

                    <div className="flex items-center gap-4 justify-center md:justify-start">
                        <div className="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0">
                            <Zap className="w-6 h-6" />
                        </div>
                        <div>
                            <div className="font-extrabold text-sm text-slate-900 dark:text-white">
                                Versement Espèces en Agence
                            </div>
                            <div className="text-xs text-slate-500">
                                Réseaux de proximité et agences agréées
                            </div>
                        </div>
                    </div>

                    <div className="flex items-center gap-4 justify-center md:justify-start">
                        <div className="w-12 h-12 rounded-2xl bg-teal-500/10 text-teal-600 flex items-center justify-center shrink-0">
                            <ShieldCheck className="w-6 h-6" />
                        </div>
                        <div>
                            <div className="font-extrabold text-sm text-slate-900 dark:text-white">
                                Facture Légale d'Entreprise
                            </div>
                            <div className="text-xs text-slate-500">
                                Reçu certifié avec ICE & TVA pour comptabilité
                            </div>
                        </div>
                    </div>
                </div>
            </Card>

            {/* FAQ Accordion */}
            <div className="max-w-3xl mx-auto space-y-6">
                <div className="text-center space-y-2">
                    <h2 className="text-2xl font-bold text-slate-900 dark:text-white">
                        Questions Fréquentes
                    </h2>
                    <p className="text-xs text-slate-500">
                        Tout savoir sur nos forfaits, les cartes physiques et les modes de règlement au Maroc.
                    </p>
                </div>

                <div className="space-y-3">
                    <Card className="p-5 rounded-2xl border border-slate-200 dark:border-slate-800">
                        <h4 className="font-bold text-sm text-slate-900 dark:text-white">
                            Comment s'effectue la livraison de ma carte au Maroc ?
                        </h4>
                        <p className="text-xs text-slate-600 dark:text-slate-400 mt-1.5 leading-relaxed">
                            Dès réception et validation de votre preuve de paiement, nos ateliers lancent la gravure de vos cartes. La livraison express est assurée sous 24 à 48 heures dans toutes les villes du Maroc (Casablanca, Rabat, Marrakech, Tanger, Fès, etc.).
                        </p>
                    </Card>

                    <Card className="p-5 rounded-2xl border border-slate-200 dark:border-slate-800">
                        <h4 className="font-bold text-sm text-slate-900 dark:text-white">
                            Comment fonctionnent les crédits de modification ?
                        </h4>
                        <p className="text-xs text-slate-600 dark:text-slate-400 mt-1.5 leading-relaxed">
                            Votre première publication est toujours 100% gratuite. Par la suite, chaque modification publiée utilise 1 crédit de votre forfait (les republications sans changement ne consomment aucun crédit). Chaque profil dispose de son propre quota strictement isolé.
                        </p>
                    </Card>

                    <Card className="p-5 rounded-2xl border border-slate-200 dark:border-slate-800">
                        <h4 className="font-bold text-sm text-slate-900 dark:text-white">
                            Puis-je mélanger différents designs de cartes avec le pack Trio ?
                        </h4>
                        <p className="text-xs text-slate-600 dark:text-slate-400 mt-1.5 leading-relaxed">
                            Absolument ! Lors de votre commande, vous choisissez individuellement la finition de chaque carte (par exemple : 1 Or Royal Brossé et 2 Noir Obsidienne Mat).
                        </p>
                    </Card>
                </div>
            </div>
        </div>
    );

    if (isAuthenticated) {
        return (
            <AppLayout title="Offres & Tarifs - AURA CARD">
                <Head title="Offres & Tarifs Commerciaux" />
                {content}
            </AppLayout>
        );
    }

    return (
        <div className="min-h-screen bg-[#F7F8FC] dark:bg-[#0B1020] text-[#111827] dark:text-slate-100">
            <Head title="Offres & Tarifs Commerciaux - AURA CARD" />
            <nav className="h-20 border-b border-slate-200 dark:border-slate-800/80 px-4 sm:px-6 flex items-center justify-between max-w-6xl mx-auto">
                <Link href="/" className="flex items-center gap-2.5">
                    <div className="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#6C5CE7] to-[#14B8A6] flex items-center justify-center text-white shadow-md shadow-[#6C5CE7]/20">
                        <CreditCard className="w-5 h-5" />
                    </div>
                    <span className="font-black text-xl tracking-tight text-slate-900 dark:text-white">
                        AURA<span className="text-[#14B8A6]">CARD</span>
                    </span>
                </Link>

                <div className="flex items-center gap-3">
                    <Link href="/login">
                        <Button variant="ghost" size="sm">Connexion</Button>
                    </Link>
                    <Link href="/register">
                        <Button variant="primary" size="sm">Créer mon compte</Button>
                    </Link>
                </div>
            </nav>

            <main>{content}</main>
        </div>
    );
}
