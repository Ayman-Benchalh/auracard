import React, { useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { PageProps } from '@/types';
import { useTranslation } from '@/Hooks/useTranslation';
import { LanguageSelector } from '@/Components/UI/LanguageSelector';
import { ThemeToggle } from '@/Components/UI/ThemeToggle';
import { Badge } from '@/Components/UI/Badge';
import { AuraCardLogo } from '@/Components/AuraCardLogo';
import {
    CreditCard,
    LayoutDashboard,
    User as UserIcon,
    Smartphone,
    BarChart3,
    Sparkles,
    Receipt,
    Shield,
    HelpCircle,
    LogOut,
    Menu,
    X,
    ChevronRight,
} from 'lucide-react';

export const AppLayout: React.FC<{ children: React.ReactNode; title?: string }> = ({
    children,
    title,
}) => {
    const { auth, brand } = usePage<PageProps>().props;
    const { url } = usePage();
    const { t, isRtl } = useTranslation();
    const [sidebarOpen, setSidebarOpen] = useState(false);

    const navItems: Array<{
        name: string;
        href: string;
        icon: any;
        current: boolean;
        disabled?: boolean;
        tag?: string;
    }> = [
        { name: t('dashboard', 'Tableau de bord'), href: '/dashboard', icon: LayoutDashboard, current: url === '/dashboard' },
        { name: t('my_profile', 'Mon Profil'), href: '/profile', icon: UserIcon, current: url.startsWith('/profile') },
        { name: t('my_card', 'Mes Cartes NFC'), href: '/cards', icon: Smartphone, current: url.startsWith('/cards') },
        { name: t('packages_pricing', 'Offres & Tarifs'), href: '/pricing', icon: Sparkles, current: url.startsWith('/pricing') || url.startsWith('/subscription') },
        { name: t('orders', 'Mes Commandes'), href: '/orders', icon: Receipt, current: url.startsWith('/orders') },
        { name: t('account', 'Mon Compte'), href: '/account', icon: Shield, current: url.startsWith('/account') },
    ];

    const accountStatusVariant = {
        active: 'success',
        suspended: 'warning',
        disabled: 'danger',
    }[auth.user?.account_status || 'active'] as 'success' | 'warning' | 'danger';

    return (
        <div className="min-h-screen flex bg-[#F7F8FC] dark:bg-[#0B1020] text-[#111827] dark:text-slate-100">
            {/* Desktop Sidebar */}
            <aside className="hidden lg:flex lg:flex-col w-72 bg-white dark:bg-[#141A2E] border-e border-slate-200/80 dark:border-slate-800/80 z-20">
                {/* Brand */}
                <div className="h-20 px-6 flex items-center border-b border-slate-200/80 dark:border-slate-800/80">
                    <Link href="/dashboard" className="flex items-center">
                        <AuraCardLogo size="sm" showSubtitle={true} />
                    </Link>
                </div>

                {/* Navigation Links */}
                <div className="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
                    {navItems.map((item) => {
                        const Icon = item.icon;
                        return item.disabled ? (
                            <div
                                key={item.name}
                                className="flex items-center justify-between px-4 py-3 rounded-xl text-sm font-medium text-slate-400 dark:text-slate-600 cursor-not-allowed opacity-60"
                            >
                                <div className="flex items-center gap-3">
                                    <Icon className="w-5 h-5" />
                                    <span>{item.name}</span>
                                </div>
                                {item.tag && (
                                    <span className="text-[10px] px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-400">
                                        {item.tag}
                                    </span>
                                )}
                            </div>
                        ) : (
                            <Link
                                key={item.name}
                                href={item.href}
                                className={`flex items-center justify-between px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-150 ${
                                    item.current
                                        ? 'bg-[#6C5CE7] text-white shadow-md shadow-[#6C5CE7]/20'
                                        : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-[#1E2642] hover:text-slate-900 dark:hover:text-white'
                                }`}
                            >
                                <div className="flex items-center gap-3">
                                    <Icon className="w-5 h-5" />
                                    <span>{item.name}</span>
                                </div>
                                <ChevronRight className={`w-4 h-4 opacity-50 ${isRtl ? 'rotate-180' : ''}`} />
                            </Link>
                        );
                    })}
                </div>

                {/* User Info & Logout Footer */}
                <div className="p-4 border-t border-slate-200/80 dark:border-slate-800/80 space-y-3">
                    <div className="flex items-center justify-between">
                        <div className="flex flex-col truncate">
                            <span className="text-sm font-bold text-slate-900 dark:text-white truncate">
                                {auth.user?.name}
                            </span>
                            <span className="text-xs text-slate-400 truncate">{auth.user?.email}</span>
                        </div>
                        <Badge variant={accountStatusVariant}>
                            {t(auth.user?.account_status || 'active')}
                        </Badge>
                    </div>

                    <div className="flex items-center justify-between pt-2 gap-2">
                        <LanguageSelector />
                        <div className="flex items-center gap-1.5">
                            <ThemeToggle />
                            <Link
                                href="/logout"
                                method="post"
                                as="button"
                                className="p-2 rounded-xl text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors"
                                title={t('logout')}
                            >
                                <LogOut className="w-4 h-4" />
                            </Link>
                        </div>
                    </div>
                </div>
            </aside>

            {/* Mobile Sidebar Overlay */}
            {sidebarOpen && (
                <div
                    className="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-40 lg:hidden"
                    onClick={() => setSidebarOpen(false)}
                />
            )}

            {/* Main Shell Container */}
            <div className="flex-1 flex flex-col min-w-0 overflow-hidden">
                {/* Top Header */}
                <header className="h-20 aura-card-glass border-b border-slate-200/80 dark:border-slate-800/80 px-4 sm:px-8 flex items-center justify-between sticky top-0 z-10">
                    <div className="flex items-center gap-3">
                        <button
                            onClick={() => setSidebarOpen(!sidebarOpen)}
                            className="p-2 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 lg:hidden"
                        >
                            <Menu className="w-6 h-6" />
                        </button>
                        <h1 className="text-xl font-extrabold text-slate-900 dark:text-white truncate">
                            {title || t('dashboard')}
                        </h1>
                    </div>

                    <div className="flex items-center gap-2 sm:gap-3">
                        {brand.whatsapp && (
                            <a
                                href={`https://wa.me/${brand.whatsapp.replace(/\+/g, '')}`}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="hidden md:flex items-center gap-2 text-xs font-semibold px-3 py-1.5 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500/20 transition-colors"
                            >
                                <HelpCircle className="w-4 h-4" />
                                <span>Support</span>
                            </a>
                        )}
                        <div className="hidden sm:block">
                            <LanguageSelector />
                        </div>
                        <ThemeToggle />
                    </div>
                </header>

                {/* Page Content */}
                <main className="flex-1 p-4 sm:p-8 overflow-y-auto max-w-7xl w-full mx-auto">{children}</main>
            </div>
        </div>
    );
};
