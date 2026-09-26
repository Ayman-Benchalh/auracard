import React, { useEffect } from 'react';
import { Head, Link } from '@inertiajs/react';
import { BlockDispatcher, BlockData } from '@/Components/Profile/BlockDispatcher';
import { ArrowLeft, Sparkles, Building2, MapPin } from 'lucide-react';

interface PreviewProps {
    profile: {
        id: string;
        slug: string;
        status: string;
        display_name: string;
        headline?: string;
        bio?: string;
        company?: string;
        job_title?: string;
        location?: string;
        avatar_url?: string;
        cover_url?: string;
        primary_color?: string;
        secondary_color?: string;
        font_family?: string;
        custom_css?: string;
        profession?: any;
        template?: any;
        theme?: any;
        blocks: BlockData[];
    };
    isDraftPreview?: boolean;
}

function isProfileThemeDark(tokens?: Record<string, string>): boolean {
    if (!tokens) return false;
    if (tokens.mode === 'dark') return true;
    if (tokens.mode === 'light') return false;
    const bg = (tokens.background || '').trim().toLowerCase();
    if (!bg) return false;
    if (['#ffffff', '#fff', '#f8fafc', '#f1f5f9', '#fafafa', '#f3f4f6'].includes(bg)) {
        return false;
    }
    if (bg.startsWith('#') && (bg.length === 7 || bg.length === 4)) {
        let r = 0, g = 0, b = 0;
        if (bg.length === 7) {
            r = parseInt(bg.slice(1, 3), 16);
            g = parseInt(bg.slice(3, 5), 16);
            b = parseInt(bg.slice(5, 7), 16);
        } else {
            r = parseInt(bg[1] + bg[1], 16);
            g = parseInt(bg[2] + bg[2], 16);
            b = parseInt(bg[3] + bg[3], 16);
        }
        const lum = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
        return lum < 0.5;
    }
    return false;
}

export default function Preview({ profile, isDraftPreview = true }: PreviewProps) {
    const themeTokens = profile.theme?.tokens || {};
    const primaryColor = profile.primary_color || themeTokens.primary || '#1e293b';
    const secondaryColor = profile.secondary_color || themeTokens.secondary || '#3b82f6';
    const bgColor = themeTokens.background || '#f8fafc';
    const textColor = themeTokens.text || '#0f172a';

    const isDarkProfile = isProfileThemeDark(themeTokens);

    useEffect(() => {
        if (isDarkProfile) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        return () => {
            try {
                const saved = localStorage.getItem('aura-theme');
                if (saved === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            } catch (e) {}
        };
    }, [isDarkProfile]);

    return (
        <div
            className={`min-h-screen font-sans antialiased selection:bg-[#6C5CE7] selection:text-white ${isDarkProfile ? 'dark text-slate-100' : 'text-slate-800'}`}
            style={{
                backgroundColor: bgColor,
                color: textColor,
                fontFamily: profile.font_family || 'Inter, sans-serif',
            }}
        >
            <Head>
                <title>{`[DRAFT PREVIEW] ${profile.display_name} - AURA CARD`}</title>
                {profile.custom_css && <style>{profile.custom_css}</style>}
            </Head>

            <div className="sticky top-0 z-50 bg-amber-500 text-slate-950 px-3 sm:px-4 py-2 text-xs font-bold flex items-center justify-between gap-2 shadow-md">
                <div className="flex items-center gap-2 min-w-0">
                    <Sparkles className="w-4 h-4 shrink-0" />
                    <span className="hidden sm:inline truncate">DRAFT PREVIEW MODE — Changes are uncommitted and not publicly visible</span>
                    <span className="sm:hidden font-bold">DRAFT PREVIEW</span>
                </div>
                <Link
                    href={`/profile/${profile.id}/builder`}
                    className="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-slate-950 text-white text-[11px] font-bold"
                >
                    <ArrowLeft className="w-3.5 h-3.5" />
                    <span>Back to Builder</span>
                </Link>
            </div>

            <main className="max-w-md mx-auto min-h-screen flex flex-col shadow-2xl relative bg-white/95 dark:bg-slate-950/95 border-x border-slate-200/60 dark:border-slate-800/60">
                {/* Cover Banner */}
                <div
                    className="h-44 sm:h-48 w-full relative bg-gradient-to-tr overflow-hidden"
                    style={{
                        backgroundImage: profile.cover_url
                            ? `url(${profile.cover_url})`
                            : `linear-gradient(135deg, ${primaryColor}, ${secondaryColor})`,
                        backgroundSize: 'cover',
                        backgroundPosition: 'center',
                    }}
                >
                    <div className="absolute inset-0 bg-slate-950/20 backdrop-blur-[2px]" />
                </div>

                {/* Avatar and Header Info */}
                <div className="px-6 pb-6 pt-0 relative -mt-16 space-y-4">
                    <div className="w-28 h-28 rounded-3xl p-1 bg-white dark:bg-slate-900 shadow-xl overflow-hidden">
                        {profile.avatar_url ? (
                            <img
                                src={profile.avatar_url}
                                alt={profile.display_name}
                                className="w-full h-full object-cover rounded-2xl"
                            />
                        ) : (
                            <div
                                className="w-full h-full rounded-2xl flex items-center justify-center text-white font-extrabold text-3xl"
                                style={{ backgroundColor: primaryColor }}
                            >
                                {profile.display_name.charAt(0).toUpperCase()}
                            </div>
                        )}
                    </div>

                    {/* Name & Titles */}
                    <div className="space-y-1">
                        <h1 className="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                            {profile.display_name}
                        </h1>

                        {profile.headline && (
                            <p className="text-sm font-semibold text-slate-600 dark:text-slate-300">
                                {profile.headline}
                            </p>
                        )}

                        <div className="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500 dark:text-slate-400 pt-1">
                            {profile.company && (
                                <div className="flex items-center gap-1">
                                    <Building2 className="w-3.5 h-3.5" />
                                    <span>{profile.company}</span>
                                </div>
                            )}
                            {profile.location && (
                                <div className="flex items-center gap-1">
                                    <MapPin className="w-3.5 h-3.5 text-rose-500" />
                                    <span>{profile.location}</span>
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                {/* Modular Blocks Stack */}
                <div className="px-6 py-4 space-y-6 flex-1">
                    {profile.blocks.map((block) => (
                        <BlockDispatcher
                            key={block.id}
                            block={block}
                            themeTokens={themeTokens}
                            primaryColor={primaryColor}
                            secondaryColor={secondaryColor}
                        />
                    ))}
                </div>

                {/* Footer Brand */}
                <footer className="mt-auto p-6 text-center border-t border-slate-100 dark:border-slate-800 space-y-2">
                    <p className="text-[11px] text-slate-400 font-medium">
                        Powered by <span className="font-extrabold text-slate-700 dark:text-slate-300">AURA CARD</span>
                    </p>
                </footer>
            </main>
        </div>
    );
}
