import React from 'react';
import { useTheme } from '@/Hooks/useTheme';
import { useTranslation } from '@/Hooks/useTranslation';
import { Sun, Moon } from 'lucide-react';

interface ThemeToggleProps {
    className?: string;
    showLabel?: boolean;
}

export const ThemeToggle: React.FC<ThemeToggleProps> = ({ className = '', showLabel = false }) => {
    const { isDark, toggleTheme } = useTheme();
    const { t } = useTranslation();

    const label = isDark ? t('theme_dark', 'Mode Sombre') : t('theme_light', 'Mode Clair');
    const title = isDark
        ? t('switch_to_light', 'Passer en mode clair')
        : t('switch_to_dark', 'Passer en mode sombre');

    return (
        <button
            type="button"
            onClick={toggleTheme}
            className={`group relative inline-flex items-center justify-center gap-2 p-2 rounded-xl transition-all duration-200 
                bg-slate-100 hover:bg-slate-200/80 text-slate-700 border border-slate-200/90
                dark:bg-[#1E2642] dark:hover:bg-[#283358] dark:text-slate-200 dark:border-slate-700/80
                focus:outline-hidden focus-visible:ring-2 focus-visible:ring-[#6C5CE7] focus-visible:ring-offset-2
                active:scale-95 shadow-xs ${className}`}
            title={title}
            aria-label={title}
        >
            <div className="relative w-4 h-4 flex items-center justify-center">
                {isDark ? (
                    <Moon className="w-4 h-4 text-indigo-300 group-hover:text-white transition-transform duration-200 group-hover:-rotate-12" />
                ) : (
                    <Sun className="w-4 h-4 text-amber-500 group-hover:text-amber-600 transition-transform duration-200 group-hover:rotate-45" />
                )}
            </div>

            {showLabel && (
                <span className="text-xs font-semibold select-none">
                    {label}
                </span>
            )}
        </button>
    );
};
