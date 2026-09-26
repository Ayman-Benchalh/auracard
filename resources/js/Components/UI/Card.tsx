import React from 'react';
import { clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export interface CardProps {
    children: React.ReactNode;
    className?: string;
    glass?: boolean;
}

export const Card: React.FC<CardProps> = ({
    children,
    className,
    glass = false,
}) => {
    return (
        <div
            className={twMerge(
                clsx(
                    'rounded-2xl p-6 border transition-all duration-200',
                    glass
                        ? 'aura-card-glass border-slate-200/80 dark:border-slate-800/80 shadow-xl'
                        : 'bg-white dark:bg-[#141A2E] border-slate-200/90 dark:border-slate-800/90 shadow-sm shadow-slate-200/50 dark:shadow-none',
                    className
                )
            )}
        >
            {children}
        </div>
    );
};
