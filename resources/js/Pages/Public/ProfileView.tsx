import React, { useState, useEffect } from 'react';
import { Head } from '@inertiajs/react';
import { BlockDispatcher, BlockData } from '@/Components/Profile/BlockDispatcher';
import {
    Download,
    Share2,
    QrCode,
    Check,
    Copy,
    Sparkles,
    ShieldCheck,
    X,
    MapPin,
    Building2,
    Briefcase,
} from 'lucide-react';

interface ProfileViewProps {
    profile: {
        id: string;
        slug: string;
        version: number;
        published_at?: string;
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
        profession?: { id: string; slug: string; name: Record<string, string>; icon?: string };
        template?: { id: string; slug: string; name: Record<string, string> };
        theme?: { id: string; slug: string; name: Record<string, string>; tokens?: Record<string, string> };
        blocks: BlockData[];
    };
    meta: {
        title: string;
        description: string;
        image: string;
        canonical_url: string;
        author: string;
    };
    vcard_url: string;
    qr_url: string;
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

export default function ProfileView({ profile, meta, vcard_url, qr_url }: ProfileViewProps) {
    const [qrModalOpen, setQrModalOpen] = useState(false);
    const [copied, setCopied] = useState(false);

    const themeTokens = profile.theme?.tokens || {};
    const primaryColor = profile.primary_color || themeTokens.primary || '#1e293b';
    const secondaryColor = profile.secondary_color || themeTokens.secondary || '#3b82f6';
    const bgColor = themeTokens.background || '#f8fafc';
    const cardBgColor = themeTokens.card_background || '#ffffff';
    const textColor = themeTokens.text || '#0f172a';

    const isDarkProfile = isProfileThemeDark(themeTokens);

    useEffect(() => {
        // Enforce the profile's own theme configuration on html tag
        if (isDarkProfile) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        return () => {
            // Restore viewer's local dashboard theme when leaving public profile
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

    const handleShare = async () => {
        if (navigator.share) {
            try {
                await navigator.share({
                    title: meta.title,
                    text: meta.description,
                    url: window.location.href,
                });
                return;
            } catch (err) {
                // fallback to modal
            }
        }
        setQrModalOpen(true);
    };

    const handleCopy = () => {
        navigator.clipboard.writeText(window.location.href);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

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
                <title>{meta.title}</title>
                <meta name="description" content={meta.description} />
                <link rel="canonical" href={meta.canonical_url} />

                {/* OpenGraph */}
                <meta property="og:title" content={meta.title} />
                <meta property="og:description" content={meta.description} />
                <meta property="og:image" content={meta.image} />
                <meta property="og:url" content={meta.canonical_url} />
                <meta property="og:type" content="profile" />

                {/* Twitter */}
                <meta name="twitter:card" content="summary_large_image" />
                <meta name="twitter:title" content={meta.title} />
                <meta name="twitter:description" content={meta.description} />
                <meta name="twitter:image" content={meta.image} />

                {/* Custom CSS if defined */}
                {profile.custom_css && <style>{profile.custom_css}</style>}
            </Head>

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

                    {/* Top action pill */}
                    <div className="absolute top-4 end-4 flex items-center gap-2 z-10">
                        <button
                            onClick={handleShare}
                            className="p-2.5 rounded-full bg-white/30 backdrop-blur-md text-white hover:bg-white/40 transition-colors shadow-lg"
                            title="Share Profile"
                        >
                            <Share2 className="w-4 h-4" />
                        </button>
                        <button
                            onClick={() => setQrModalOpen(true)}
                            className="p-2.5 rounded-full bg-white/30 backdrop-blur-md text-white hover:bg-white/40 transition-colors shadow-lg"
                            title="QR Code"
                        >
                            <QrCode className="w-4 h-4" />
                        </button>
                    </div>
                </div>

                {/* Avatar and Header Info */}
                <div className="px-6 pb-6 pt-0 relative -mt-16 space-y-4">
                    <div className="flex items-end justify-between">
                        <div className="relative">
                            <div
                                className="w-28 h-28 rounded-3xl p-1 bg-white dark:bg-slate-900 shadow-xl overflow-hidden"
                                style={{
                                    boxShadow: `0 10px 25px -5px ${primaryColor}30`,
                                }}
                            >
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
                            <div className="absolute -bottom-1 -end-1 p-1 rounded-full bg-emerald-500 text-white shadow-md">
                                <ShieldCheck className="w-4 h-4" />
                            </div>
                        </div>

                        {/* Verified Identity Badge */}
                        <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-[11px] font-bold text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                            <Sparkles className="w-3 h-3 text-amber-500" />
                            <span>AURA Verified</span>
                        </div>
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

                    {/* Primary CTA: Save Contact */}
                    <div className="pt-2">
                        <a
                            href={vcard_url}
                            className="w-full flex items-center justify-center gap-2.5 py-3.5 px-6 rounded-2xl font-bold text-sm text-white shadow-lg hover:opacity-95 transition-all transform active:scale-[0.99]"
                            style={{
                                backgroundColor: primaryColor,
                                boxShadow: `0 8px 20px -4px ${primaryColor}50`,
                            }}
                        >
                            <Download className="w-4 h-4" />
                            <span>Save to Contacts (.vcf)</span>
                        </a>
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
                            profileSlug={profile.slug}
                        />
                    ))}
                </div>

                {/* Footer Brand */}
                <footer className="mt-auto p-6 text-center border-t border-slate-100 dark:border-slate-800 space-y-2">
                    <p className="text-[11px] text-slate-400 font-medium">
                        Powered by <span className="font-extrabold text-slate-700 dark:text-slate-300">AURA CARD</span>
                    </p>
                    <p className="text-[10px] text-slate-400">
                        Contactless Digital Identity & Professional Networking
                    </p>
                </footer>
            </main>

            {/* Share / QR Modal */}
            {qrModalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs">
                    <div className="relative w-full max-w-sm rounded-3xl bg-white dark:bg-slate-900 p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-6">
                        <button
                            onClick={() => setQrModalOpen(false)}
                            className="absolute top-4 end-4 p-2 rounded-full text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                        >
                            <X className="w-5 h-5" />
                        </button>

                        <div className="text-center space-y-1">
                            <h3 className="text-lg font-extrabold text-slate-900 dark:text-white">Scan Contact Card</h3>
                            <p className="text-xs text-slate-500">Scan with any smartphone camera to open profile</p>
                        </div>

                        {/* High-contrast QR SVG Container */}
                        <div className="p-4 rounded-2xl bg-white shadow-inner border border-slate-100 flex items-center justify-center">
                            <img
                                src={qr_url}
                                alt={`QR Code for ${profile.display_name}`}
                                className="w-48 sm:w-56 h-auto max-w-full aspect-square object-contain"
                            />
                        </div>

                        {/* Copy Link Input */}
                        <div className="flex items-center gap-2 p-2 rounded-2xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                            <input
                                type="text"
                                readOnly
                                value={meta.canonical_url}
                                className="w-full bg-transparent text-xs text-slate-700 dark:text-slate-200 font-mono px-2 focus:outline-hidden"
                            />
                            <button
                                onClick={handleCopy}
                                className="p-2 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-bold text-xs flex items-center gap-1.5 flex-shrink-0"
                            >
                                {copied ? <Check className="w-3.5 h-3.5" /> : <Copy className="w-3.5 h-3.5" />}
                                <span>{copied ? 'Copied' : 'Copy'}</span>
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
