import React, { InputHTMLAttributes, forwardRef } from 'react';
import { clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export interface InputProps extends InputHTMLAttributes<HTMLInputElement> {
    label?: string;
    error?: string;
    helperText?: string;
    startSlot?: React.ReactNode;
    endSlot?: React.ReactNode;
}

export const Input = forwardRef<HTMLInputElement, InputProps>(
    ({ label, error, helperText, startSlot, endSlot, className, id, ...props }, ref) => {
        const inputId = id || (label ? label.toLowerCase().replace(/\s+/g, '-') : undefined);

        return (
            <div className="w-full">
                {label && (
                    <label
                        htmlFor={inputId}
                        className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5"
                    >
                        {label}
                    </label>
                )}
                <div className="relative flex items-center">
                    {startSlot && (
                        <div className="absolute inset-y-0 start-0 ps-3 flex items-center pointer-events-none text-slate-400">
                            {startSlot}
                        </div>
                    )}
                    <input
                        id={inputId}
                        ref={ref}
                        className={twMerge(
                            clsx(
                                'w-full px-4 py-2.5 min-h-[44px] rounded-xl text-sm transition-all duration-200',
                                'bg-white dark:bg-[#141A2E] text-slate-900 dark:text-slate-100',
                                'border border-slate-300 dark:border-slate-700',
                                'focus:outline-none focus:ring-2 focus:ring-[#6C5CE7] focus:border-transparent',
                                'placeholder:text-slate-400 dark:placeholder:text-slate-500',
                                startSlot && 'ps-10',
                                endSlot && 'pe-10',
                                error && 'border-rose-500 focus:ring-rose-500',
                                className
                            )
                        )}
                        {...props}
                    />
                    {endSlot && (
                        <div className="absolute inset-y-0 end-0 pe-3 flex items-center">
                            {endSlot}
                        </div>
                    )}
                </div>
                {error && <p className="mt-1.5 text-xs text-rose-500 font-medium">{error}</p>}
                {!error && helperText && (
                    <p className="mt-1.5 text-xs text-slate-500 dark:text-slate-400">{helperText}</p>
                )}
            </div>
        );
    }
);

Input.displayName = 'Input';
