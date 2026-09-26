import React from 'react';
import { Link } from '@inertiajs/react';
import { CreditCard } from 'lucide-react';
import { LanguageSelector } from '@/Components/UI/LanguageSelector';
import { ThemeToggle } from '@/Components/UI/ThemeToggle';
import { useTranslation } from '@/Hooks/useTranslation';

export const GuestLayout: React.FC<{ children: React.ReactNode; title?: string }> = ({
    children,
    title,
}) => {
    const { t } = useTranslation();

    return (
        <div className="min-h-screen flex flex-col justify-between bg-[#F7F8FC] dark:bg-[#0B1020] text-[#111827] dark:text-slate-100 p-4 sm:p-6 lg:p-8">
            {/* Top Bar */}
            <div className="max-w-md w-full mx-auto flex items-center justify-between">
                <Link href="/" className="flex items-center gap-2.5 group">
                    <div className="w-9 h-9 rounded-xl bg-gradient-to-tr from-[#6C5CE7] to-[#14B8A6] flex items-center justify-center text-white shadow-md shadow-[#6C5CE7]/20 group-hover:scale-105 transition-transform">
                        <CreditCard className="w-4 h-4" />
                    </div>
                    <span className="font-extrabold text-lg tracking-tight text-slate-900 dark:text-white">
                        AURA<span className="text-[#14B8A6]">CARD</span>
                    </span>
                </Link>

                <div className="flex items-center gap-2">
                    <ThemeToggle />
                    <LanguageSelector />
                </div>
            </div>

            {/* Auth Box */}
            <div className="max-w-md w-full mx-auto my-8">
                <div className="aura-card-glass bg-white/95 dark:bg-[#141A2E]/95 border border-slate-200/80 dark:border-slate-800/80 rounded-3xl p-6 sm:p-8 shadow-2xl shadow-slate-300/40 dark:shadow-none">
                    {title && (
                        <h2 className="text-xl font-bold text-slate-900 dark:text-white mb-6 text-center">
                            {title}
                        </h2>
                    )}
                    {children}
                </div>
            </div>

            {/* Bottom Copy */}
            <div className="text-center text-xs text-slate-400">
                AURA CARD &copy; {new Date().getFullYear()} — {t('tagline')}
            </div>
        </div>
    );
};
