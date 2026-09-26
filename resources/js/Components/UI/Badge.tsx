import React from 'react';
import { clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export interface BadgeProps {
    children: React.ReactNode;
    variant?: 'primary' | 'secondary' | 'success' | 'warning' | 'danger' | 'neutral';
    className?: string;
}

export const Badge: React.FC<BadgeProps> = ({
    children,
    variant = 'primary',
    className,
}) => {
    const variants = {
        primary: 'bg-[#6C5CE7]/15 text-[#6C5CE7] dark:bg-[#6C5CE7]/25 dark:text-indigo-300 border border-[#6C5CE7]/30',
        secondary: 'bg-[#14B8A6]/15 text-[#14B8A6] dark:bg-[#14B8A6]/25 dark:text-teal-300 border border-[#14B8A6]/30',
        success: 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-500/30',
        warning: 'bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-500/30',
        danger: 'bg-rose-500/15 text-rose-700 dark:text-rose-300 border border-rose-500/30',
        neutral: 'bg-slate-500/15 text-slate-700 dark:text-slate-300 border border-slate-500/30',
    };

    return (
        <span
            className={twMerge(
                clsx(
                    'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold tracking-wide',
                    variants[variant],
                    className
                )
            )}
        >
            {children}
        </span>
    );
};
