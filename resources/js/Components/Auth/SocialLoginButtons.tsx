import React from 'react';
import { usePage } from '@inertiajs/react';
import { useTranslation } from '@/Hooks/useTranslation';

interface SocialLoginButtonsProps {
    mode?: 'login' | 'register';
    className?: string;
}

export const SocialLoginButtons: React.FC<SocialLoginButtonsProps> = ({
    mode = 'login',
    className = '',
}) => {
    const { t } = useTranslation();
    const { props } = usePage<{
        socialAuth?: {
            google?: boolean;
            facebook?: boolean;
            instagram?: boolean;
        };
    }>();

    const socialAuth = props.socialAuth || {
        google: true,
        facebook: true,
        instagram: false,
    };

    const hasAnySocial = socialAuth.google || socialAuth.facebook || socialAuth.instagram;

    if (!hasAnySocial) {
        return null;
    }

    return (
        <div className={`space-y-3.5 ${className}`}>
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                {socialAuth.google && (
                    <a
                        href="/auth/google/redirect"
                        className="group relative flex items-center justify-center gap-2.5 px-4 py-2.5 min-h-[44px] rounded-xl text-sm font-medium border border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-900/90 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/90 hover:border-slate-300 dark:hover:border-slate-600 transition-all duration-200 shadow-sm hover:shadow"
                        title={t('continue_with_google')}
                    >
                        <svg className="w-4 h-4 shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                            <path
                                fill="#4285F4"
                                d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.8-2.4 3.66v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.15z"
                            />
                            <path
                                fill="#34A853"
                                d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.24v3.15C3.26 21.36 7.33 24 12 24z"
                            />
                            <path
                                fill="#FBBC05"
                                d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.24C.45 8.16 0 9.98 0 12s.45 3.84 1.24 5.42l4.04-3.15z"
                            />
                            <path
                                fill="#EA4335"
                                d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.24 6.58l4.04 3.15c.95-2.83 3.6-4.98 6.72-4.98z"
                            />
                        </svg>
                        <span className="truncate">Google</span>
                    </a>
                )}

                {socialAuth.facebook && (
                    <a
                        href="/auth/facebook/redirect"
                        className="group relative flex items-center justify-center gap-2.5 px-4 py-2.5 min-h-[44px] rounded-xl text-sm font-medium border border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-900/90 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/90 hover:border-slate-300 dark:hover:border-slate-600 transition-all duration-200 shadow-sm hover:shadow"
                        title={t('continue_with_facebook')}
                    >
                        <svg className="w-4 h-4 shrink-0 text-[#1877F2]" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                        </svg>
                        <span className="truncate">Facebook</span>
                    </a>
                )}

                {socialAuth.instagram && (
                    <a
                        href="/auth/instagram/redirect"
                        className="group relative flex items-center justify-center gap-2.5 px-4 py-2.5 min-h-[44px] rounded-xl text-sm font-medium border border-slate-200 dark:border-slate-700/80 bg-white dark:bg-slate-900/90 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/90 hover:border-slate-300 dark:hover:border-slate-600 transition-all duration-200 shadow-sm hover:shadow"
                        title={t('continue_with_instagram')}
                    >
                        <svg className="w-4 h-4 shrink-0 text-[#E4405F]" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                        </svg>
                        <span className="truncate">Instagram</span>
                    </a>
                )}
            </div>

            <div className="relative flex items-center justify-center my-4">
                <div className="border-t border-slate-200 dark:border-slate-700/80 w-full" />
                <span className="bg-white dark:bg-[#141A2E] px-3 text-xs uppercase tracking-wider text-slate-400 dark:text-slate-500 font-medium select-none shrink-0">
                    {mode === 'register' ? t('or_continue_with_email') : t('or_continue_with_email')}
                </span>
            </div>
        </div>
    );
};
