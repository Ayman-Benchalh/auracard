import React from 'react';
import { Link } from '@inertiajs/react';
import { CreditCard, Wifi, ShieldCheck, Zap } from 'lucide-react';
import { LanguageSelector } from '@/Components/UI/LanguageSelector';
import { ThemeToggle } from '@/Components/UI/ThemeToggle';
import { AuraCardLogo } from '@/Components/AuraCardLogo';
import { useTranslation } from '@/Hooks/useTranslation';

interface GuestLayoutProps {
    children: React.ReactNode;
    title?: string;
    subtitle?: string;
}

export const GuestLayout: React.FC<GuestLayoutProps> = ({
    children,
    title,
    subtitle,
}) => {
    const { t } = useTranslation();

    return (
        <div className="min-h-screen flex flex-col justify-between bg-slate-50 dark:bg-[#0B1020] text-slate-900 dark:text-slate-100 transition-colors duration-200">
            {/* Top Navigation */}
            <header className="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 flex items-center justify-between">
                <Link href="/" className="inline-flex items-center transition-transform hover:opacity-95">
                    <AuraCardLogo size="md" />
                </Link>

                <div className="flex items-center gap-2 sm:gap-3">
                    <ThemeToggle />
                    <LanguageSelector />
                </div>
            </header>

            {/* Main Content Area */}
            <main className="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10 flex-1 flex items-center">
                <div className="w-full grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                    {/* Left Showcase (Senior Product Physical Presentation - Desktop only) */}
                    <div className="hidden lg:flex lg:col-span-6 flex-col justify-center space-y-8 pe-4">
                        <div className="space-y-4">
                            <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold tracking-wide uppercase bg-[#6C5CE7]/10 dark:bg-[#6C5CE7]/20 text-[#6C5CE7] dark:text-[#818CF8] border border-[#6C5CE7]/20">
                                <CreditCard className="w-3.5 h-3.5" />
                                <span>Cartes NFC Professionnelles au Maroc</span>
                            </div>

                            <h1 className="text-3xl xl:text-4xl font-black tracking-tight text-slate-900 dark:text-white leading-tight">
                                {t('hero_title')}
                            </h1>

                            <p className="text-base text-slate-600 dark:text-slate-300 leading-relaxed max-w-xl">
                                {t('secure_hardware_tagline')}
                            </p>
                        </div>

                        {/* Physical Card Mockup */}
                        <div className="relative group max-w-md w-full select-none">
                            <div className="absolute -inset-1 bg-gradient-to-r from-[#6C5CE7] to-[#14B8A6] rounded-2xl blur-lg opacity-25 group-hover:opacity-40 transition duration-500" />
                            <div className="relative bg-gradient-to-br from-slate-900 via-[#111827] to-slate-950 border border-slate-700/80 rounded-2xl p-6 sm:p-7 shadow-2xl text-white">
                                {/* Card Header */}
                                <div className="flex items-center justify-between mb-8">
                                    <div className="flex items-center gap-2">
                                        <span className="font-black text-lg tracking-wider text-white">AURA</span>
                                        <span className="text-[#14B8A6] font-bold text-sm tracking-widest">CARD</span>
                                    </div>
                                    <div className="flex items-center gap-2 text-slate-400">
                                        <Wifi className="w-5 h-5 rotate-90 text-[#14B8A6]" />
                                        <span className="text-[10px] tracking-wider font-mono uppercase bg-slate-800 px-2 py-0.5 rounded text-slate-300">NTAG216</span>
                                    </div>
                                </div>

                                {/* EMV Chip Visual */}
                                <div className="w-11 h-8 rounded-md bg-gradient-to-br from-amber-200 via-amber-400 to-amber-600 border border-amber-300/60 shadow-inner mb-6 relative overflow-hidden">
                                    <div className="absolute inset-0 border border-amber-700/30 rounded-sm m-0.5" />
                                    <div className="absolute top-1/2 left-0 right-0 h-px bg-amber-800/40" />
                                    <div className="absolute left-1/3 top-0 bottom-0 w-px bg-amber-800/40" />
                                    <div className="absolute right-1/3 top-0 bottom-0 w-px bg-amber-800/40" />
                                </div>

                                {/* Cardholder Details */}
                                <div className="flex items-end justify-between">
                                    <div>
                                        <div className="text-xs uppercase tracking-widest text-slate-400 font-mono mb-1">
                                            Executive Edition
                                        </div>
                                        <div className="text-base font-bold text-white tracking-wide">
                                            Yassine Bennani
                                        </div>
                                        <div className="text-xs text-[#14B8A6] font-medium">
                                            Cabinet Bennani &amp; Associés
                                        </div>
                                    </div>
                                    <div className="text-end">
                                        <span className="text-[10px] uppercase font-mono tracking-widest text-slate-400 block">
                                            Finition
                                        </span>
                                        <span className="text-xs font-semibold text-slate-200">
                                            {t('finishes_matte_black')}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Value propositions */}
                        <div className="grid grid-cols-2 gap-3 text-xs text-slate-600 dark:text-slate-300 font-medium">
                            <div className="flex items-center gap-2">
                                <Zap className="w-4 h-4 text-[#14B8A6] shrink-0" />
                                <span>{t('zero_app_visitor_access')}</span>
                            </div>
                            <div className="flex items-center gap-2">
                                <ShieldCheck className="w-4 h-4 text-[#6C5CE7] shrink-0" />
                                <span>Puce cryptographique NTAG216</span>
                            </div>
                        </div>
                    </div>

                    {/* Right Form Container */}
                    <div className="w-full lg:col-span-6 flex justify-center">
                        <div className="w-full max-w-md bg-white dark:bg-[#141A2E] border border-slate-200/90 dark:border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl shadow-slate-200/50 dark:shadow-none">
                            {title && (
                                <div className="mb-6 text-center sm:text-start">
                                    <h2 className="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                                        {title}
                                    </h2>
                                    {subtitle && (
                                        <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                            {subtitle}
                                        </p>
                                    )}
                                </div>
                            )}

                            {children}
                        </div>
                    </div>
                </div>
            </main>

            {/* Bottom Footer */}
            <footer className="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 text-center text-xs text-slate-500 dark:text-slate-400">
                <p>
                    AURA CARD &copy; {new Date().getFullYear()} — {t('tagline')}
                </p>
            </footer>
        </div>
    );
};
