import React from 'react';
import { useTranslation } from '@/Hooks/useTranslation';
import { Globe } from 'lucide-react';

export const LanguageSelector: React.FC = () => {
    const { locale } = useTranslation();

    const languages = [
        { code: 'ar', label: 'العربية' },
        { code: 'fr', label: 'Français' },
        { code: 'en', label: 'English' },
    ];

    return (
        <div className="flex items-center gap-1.5 p-1 rounded-xl bg-slate-100 dark:bg-[#1E2642] border border-slate-200 dark:border-slate-700">
            <Globe className="w-3.5 h-3.5 ms-1.5 text-slate-500" />
            <div className="flex gap-1">
                {languages.map((lang) => (
                    <a
                        key={lang.code}
                        href={`/locale/${lang.code}`}
                        className={`px-2.5 py-1 text-xs font-semibold rounded-lg transition-all duration-150 ${
                            locale === lang.code
                                ? 'bg-white dark:bg-[#6C5CE7] text-[#6C5CE7] dark:text-white shadow-sm'
                                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
                        }`}
                    >
                        {lang.label}
                    </a>
                ))}
            </div>
        </div>
    );
};
