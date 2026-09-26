import React, { useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { PageProps } from '@/types';
import { useTranslation } from '@/Hooks/useTranslation';
import { LanguageSelector } from '@/Components/UI/LanguageSelector';
import { ThemeToggle } from '@/Components/UI/ThemeToggle';
import { Button } from '@/Components/UI/Button';
import { AuraCardLogo } from '@/Components/AuraCardLogo';
import { MessageCircle, ArrowRight, Menu, X, ShieldCheck } from 'lucide-react';

export const MarketingLayout: React.FC<{ children: React.ReactNode }> = ({ children }) => {
    const { auth, brand } = usePage<PageProps>().props;
    const { t, isRtl, locale } = useTranslation();
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

    const navItems = [
        { label: locale === 'ar' ? 'البطاقات والتصاميم' : locale === 'en' ? 'Smart Cards' : 'Cartes NFC', href: '#products' },
        { label: locale === 'ar' ? 'كيف تعمل' : locale === 'en' ? 'How it Works' : 'Fonctionnement', href: '#how-it-works' },
        { label: locale === 'ar' ? 'الميزات' : locale === 'en' ? 'Features' : 'Fonctionnalités', href: '#features' },
        { label: locale === 'ar' ? 'المهن' : locale === 'en' ? 'Professions' : 'Pour qui ?', href: '#professions' },
        { label: locale === 'ar' ? 'الباقات والأسعار' : locale === 'en' ? 'Pricing' : 'Tarifs', href: '#pricing' },
        { label: 'FAQ', href: '#faq' },
    ];

    return (
        <div className="min-h-screen flex flex-col bg-[#F8FAFC] dark:bg-[#080D1A] text-slate-900 dark:text-slate-100 selection:bg-[#6C5CE7] selection:text-white">
            {/* Navigation Header */}
            <header className="sticky top-0 z-50 backdrop-blur-md bg-white/85 dark:bg-[#0B1120]/85 border-b border-slate-200/80 dark:border-slate-800/80 transition-colors">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
                    {/* Brand Logo */}
                    <Link href="/" className="flex items-center">
                        <AuraCardLogo size="md" showSubtitle={true} />
                    </Link>

                    {/* Desktop Center Menu */}
                    <nav className="hidden lg:flex items-center gap-7 text-sm font-semibold text-slate-600 dark:text-slate-300">
                        {navItems.map((item) => (
                            <a
                                key={item.label}
                                href={item.href}
                                className="hover:text-[#6C5CE7] dark:hover:text-[#818CF8] transition-colors"
                            >
                                {item.label}
                            </a>
                        ))}
                    </nav>

                    {/* Desktop Right Actions */}
                    <div className="hidden md:flex items-center gap-3">
                        <ThemeToggle />
                        <LanguageSelector />

                        {auth.user ? (
                            <Link href="/dashboard">
                                <Button variant="primary" size="sm" className="rounded-xl shadow-md shadow-[#6C5CE7]/20 font-bold">
                                    <span>{t('dashboard')}</span>
                                    <ArrowRight className={`w-4 h-4 ${isRtl ? 'rotate-180 me-1' : 'ms-1'}`} />
                                </Button>
                            </Link>
                        ) : (
                            <div className="flex items-center gap-2">
                                <Link href="/login">
                                    <Button variant="ghost" size="sm" className="font-semibold text-slate-700 dark:text-slate-200">
                                        {t('login')}
                                    </Button>
                                </Link>
                                <Link href="/register">
                                    <Button variant="primary" size="sm" className="rounded-xl shadow-md shadow-[#6C5CE7]/20 font-bold">
                                        {t('register')}
                                    </Button>
                                </Link>
                            </div>
                        )}
                    </div>

                    {/* Mobile Menu Toggle */}
                    <div className="flex items-center gap-2 md:hidden">
                        <ThemeToggle />
                        <LanguageSelector />
                        <button
                            type="button"
                            onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                            className="p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                            aria-label="Toggle menu"
                        >
                            {mobileMenuOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
                        </button>
                    </div>
                </div>

                {/* Mobile Dropdown */}
                {mobileMenuOpen && (
                    <div className="lg:hidden border-b border-slate-200 dark:border-slate-800 bg-white/95 dark:bg-[#0E1526]/95 backdrop-blur-xl px-4 py-6 space-y-4 shadow-xl">
                        <div className="flex flex-col gap-3">
                            {navItems.map((item) => (
                                <a
                                    key={item.label}
                                    href={item.href}
                                    onClick={() => setMobileMenuOpen(false)}
                                    className="px-3 py-2 rounded-lg text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                                >
                                    {item.label}
                                </a>
                            ))}
                        </div>

                        <div className="pt-4 border-t border-slate-200 dark:border-slate-800 flex flex-col gap-2.5">
                            {auth.user ? (
                                <Link href="/dashboard" className="w-full">
                                    <Button variant="primary" className="w-full justify-center rounded-xl font-bold">
                                        {t('dashboard')}
                                    </Button>
                                </Link>
                            ) : (
                                <>
                                    <Link href="/login" className="w-full">
                                        <Button variant="outline" className="w-full justify-center rounded-xl font-semibold">
                                            {t('login')}
                                        </Button>
                                    </Link>
                                    <Link href="/register" className="w-full">
                                        <Button variant="primary" className="w-full justify-center rounded-xl font-bold">
                                            {t('register')}
                                        </Button>
                                    </Link>
                                </>
                            )}
                        </div>
                    </div>
                )}
            </header>

            {/* Main Page Content */}
            <main className="flex-1">{children}</main>

            {/* Ultra-Premium Moroccan Corporate Footer */}
            <footer className="border-t border-slate-200 dark:border-slate-800/80 bg-white dark:bg-[#070B14] py-14 transition-colors">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <div className="md:col-span-2 space-y-4">
                            <AuraCardLogo size="md" showSubtitle={true} />
                            <p className="text-sm text-slate-500 dark:text-slate-400 max-w-md leading-relaxed">
                                {locale === 'ar'
                                    ? 'المنصة المغربية الرائدة في بطاقات الأعمال الذكية وحلول الهوية الرقمية للمحترفين والشركات. شارك بياناتك بلمسة واحدة.'
                                    : locale === 'en'
                                    ? 'The premier Moroccan platform for smart NFC business cards and unified digital identities. Tap, connect, and elevate your professional presence.'
                                    : 'La solution marocaine de cartes de visite connectées NFC et profils digitaux interactifs. Touchez, connectez et transformez vos opportunités professionnelles.'}
                            </p>
                            <div className="flex items-center gap-2 text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                <ShieldCheck className="w-4 h-4" />
                                <span>Conforme CNDP • Données Hébergées et Sécurisées</span>
                            </div>
                        </div>

                        <div className="space-y-3">
                            <h4 className="text-xs font-bold uppercase tracking-wider text-slate-400">Navigation</h4>
                            <ul className="space-y-2 text-sm text-slate-600 dark:text-slate-400 font-medium">
                                <li><a href="#products" className="hover:text-[#6C5CE7] transition-colors">Cartes & Finitions</a></li>
                                <li><a href="#how-it-works" className="hover:text-[#6C5CE7] transition-colors">Fonctionnement</a></li>
                                <li><a href="#pricing" className="hover:text-[#6C5CE7] transition-colors">Nos Forfaits</a></li>
                                <li><Link href="/pricing" className="hover:text-[#6C5CE7] transition-colors">Tous les Tarifs</Link></li>
                            </ul>
                        </div>

                        <div className="space-y-3">
                            <h4 className="text-xs font-bold uppercase tracking-wider text-slate-400">Contact & Support</h4>
                            <div className="space-y-2 text-sm text-slate-600 dark:text-slate-400">
                                {brand.whatsapp && (
                                    <a
                                        href={`https://wa.me/${brand.whatsapp.replace(/\+/g, '')}`}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="inline-flex items-center gap-2 text-emerald-600 dark:text-emerald-400 font-semibold hover:underline"
                                    >
                                        <MessageCircle className="w-4 h-4" />
                                        <span>WhatsApp Support</span>
                                    </a>
                                )}
                                <div className="text-xs text-slate-500 font-mono">{brand.email}</div>
                                <div className="text-xs text-slate-400">Rabat • Casablanca • Partout au Maroc</div>
                            </div>
                        </div>
                    </div>

                    <div className="pt-8 border-t border-slate-100 dark:border-slate-800/60 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-400 font-medium">
                        <div>AURA CARD © {new Date().getFullYear()} • Tous droits réservés.</div>
                        <div className="flex items-center gap-4">
                            <span>Virement Bancaire & Espèces en Agence</span>
                            <span>•</span>
                            <span>Livraison Nationale Express</span>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    );
};
