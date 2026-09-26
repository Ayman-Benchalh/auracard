import React from 'react';

interface AuraCardLogoProps {
    variant?: 'full' | 'symbol' | 'horizontal';
    size?: 'xs' | 'sm' | 'md' | 'lg' | 'xl';
    className?: string;
    showSubtitle?: boolean;
}

export const AuraCardLogo: React.FC<AuraCardLogoProps> = ({
    variant = 'full',
    size = 'md',
    className = '',
    showSubtitle = true,
}) => {
    const sizeMap = {
        xs: { icon: 20, text: 'text-sm', sub: 'text-[9px]' },
        sm: { icon: 26, text: 'text-base', sub: 'text-[10px]' },
        md: { icon: 34, text: 'text-xl', sub: 'text-[11px]' },
        lg: { icon: 44, text: 'text-2xl', sub: 'text-xs' },
        xl: { icon: 56, text: 'text-3xl', sub: 'text-sm' },
    };

    const s = sizeMap[size];

    const SymbolSvg = (
        <svg
            width={s.icon}
            height={s.icon}
            viewBox="0 0 100 100"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            className="shrink-0 transition-transform duration-300 group-hover:scale-105"
        >
            <defs>
                <linearGradient id="auraCardGrad1" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stopColor="#6C5CE7" />
                    <stop offset="50%" stopColor="#818CF8" />
                    <stop offset="100%" stopColor="#06B6D4" />
                </linearGradient>
                <linearGradient id="auraCardGrad2" x1="100%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stopColor="#06B6D4" />
                    <stop offset="50%" stopColor="#818CF8" />
                    <stop offset="100%" stopColor="#6C5CE7" />
                </linearGradient>
                <filter id="auraGlow" x="-20%" y="-20%" width="140%" height="140%">
                    <feGaussianBlur stdDeviation="4" result="blur" />
                    <feMerge>
                        <feMergeNode in="blur" />
                        <feMergeNode in="SourceGraphic" />
                    </feMerge>
                </filter>
            </defs>

            {/* Background Card Base Frame */}
            <rect
                x="14"
                y="18"
                width="72"
                height="64"
                rx="16"
                fill="#0F172A"
                stroke="url(#auraCardGrad1)"
                strokeWidth="3.5"
            />

            {/* Overlapping Aura Halo Wave 1 */}
            <path
                d="M28 36 C38 22, 62 22, 72 36"
                stroke="url(#auraCardGrad2)"
                strokeWidth="3.5"
                strokeLinecap="round"
                opacity="0.85"
            />

            {/* Dynamic Card Chip Core */}
            <rect
                x="26"
                y="42"
                width="20"
                height="16"
                rx="4"
                fill="url(#auraCardGrad1)"
            />

            {/* Interconnected Radiating NFC Waves */}
            <path
                d="M58 40 C65 45, 65 55, 58 60"
                stroke="#38BDF8"
                strokeWidth="3.5"
                strokeLinecap="round"
                filter="url(#auraGlow)"
            />
            <path
                d="M68 34 C78 43, 78 57, 68 66"
                stroke="url(#auraCardGrad1)"
                strokeWidth="3.5"
                strokeLinecap="round"
            />

            {/* Smart Microdot Nexus */}
            <circle cx="50" cy="50" r="3" fill="#FFFFFF" />
        </svg>
    );

    if (variant === 'symbol') {
        return <div className={`inline-flex items-center ${className}`}>{SymbolSvg}</div>;
    }

    return (
        <div className={`inline-flex items-center gap-2.5 group select-none ${className}`}>
            {SymbolSvg}
            <div className="flex flex-col leading-none">
                <div className={`flex items-baseline tracking-tight font-extrabold ${s.text}`}>
                    <span className="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 dark:from-white dark:via-slate-100 dark:to-white bg-clip-text text-transparent font-black tracking-wider">
                        AURA
                    </span>
                    <span className="text-[#6C5CE7] dark:text-[#818CF8] ml-1 tracking-widest font-bold">
                        CARD
                    </span>
                </div>
                {showSubtitle && (
                    <span className={`tracking-widest uppercase font-semibold text-slate-500 dark:text-slate-400 mt-0.5 ${s.sub}`}>
                        Smart NFC Identity
                    </span>
                )}
            </div>
        </div>
    );
};

export default AuraCardLogo;
