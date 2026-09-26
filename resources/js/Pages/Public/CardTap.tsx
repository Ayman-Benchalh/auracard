import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@/Components/UI/Button';
import { Badge } from '@/Components/UI/Badge';
import {
    Smartphone,
    CreditCard,
    Sparkles,
    ShieldAlert,
    CheckCircle2,
    ArrowRight,
    Lock,
} from 'lucide-react';

interface CardTapProps {
    card: {
        serial_number: string;
        status: 'unclaimed' | 'active' | 'suspended' | 'lost' | 'replaced' | 'disabled';
        card_type?: string | null;
        raw_token?: string;
        activated_at?: string;
    };
    owner: {
        name?: string;
        email?: string;
    } | null;
}

export default function CardTap({ card, owner }: CardTapProps) {
    return (
        <div className="min-h-screen bg-[#0B1020] text-slate-100 flex flex-col justify-between p-4 sm:p-8">
            <Head title={`AURA CARD — ${card.serial_number}`} />

            {/* Header Brand */}
            <header className="max-w-md mx-auto w-full flex items-center justify-between py-4">
                <Link href="/" className="flex items-center gap-2.5">
                    <div className="w-9 h-9 rounded-xl bg-gradient-to-tr from-[#6C5CE7] to-[#14B8A6] flex items-center justify-center text-white shadow-md shadow-[#6C5CE7]/30">
                        <CreditCard className="w-5 h-5" />
                    </div>
                    <span className="font-extrabold text-lg tracking-tight text-white">
                        AURA<span className="text-[#14B8A6]">CARD</span>
                    </span>
                </Link>

                <div className="flex items-center gap-2">
                    <span className="w-2 h-2 rounded-full bg-emerald-400 animate-ping" />
                    <span className="text-[11px] font-mono uppercase text-emerald-400 font-semibold tracking-wider">
                        NFC Tag Live
                    </span>
                </div>
            </header>

            {/* Main Interactive Card Container */}
            <main className="max-w-md mx-auto w-full my-auto space-y-6">
                {/* 1. Unclaimed Fresh Card State */}
                {card.status === 'unclaimed' && (
                    <div className="bg-[#141A2E] rounded-3xl p-8 border border-slate-800 shadow-2xl text-center space-y-6 relative overflow-hidden">
                        <div className="w-16 h-16 rounded-2xl bg-gradient-to-tr from-[#6C5CE7] to-[#14B8A6] text-white flex items-center justify-center mx-auto shadow-xl shadow-[#6C5CE7]/30">
                            <Sparkles className="w-8 h-8" />
                        </div>

                        <div className="space-y-2">
                            <Badge variant="neutral" className="uppercase font-mono text-[10px]">
                                Ready For Activation
                            </Badge>
                            <h1 className="text-2xl font-extrabold text-white tracking-tight">
                                Brand New AURA CARD
                            </h1>
                            <p className="text-xs text-slate-400 leading-relaxed">
                                This physical card is ready for activation! Link it to your account to build your modular professional digital profile.
                            </p>
                        </div>

                        <div className="p-3 rounded-2xl bg-slate-900/60 border border-slate-800 font-mono text-xs text-slate-300">
                            <span className="text-slate-500 block text-[10px] uppercase">Card Serial</span>
                            <span className="text-[#14B8A6] font-bold">{card.serial_number}</span>
                        </div>

                        <div className="space-y-3 pt-2">
                            <Link href={card.raw_token ? `/cards/claim?token=${card.raw_token}` : '/cards/claim'} className="block">
                                <Button className="w-full h-12 text-sm font-bold shadow-lg shadow-[#6C5CE7]/30 inline-flex items-center justify-center gap-2">
                                    <span>Activate This Card</span>
                                    <ArrowRight className="w-4 h-4" />
                                </Button>
                            </Link>

                            <Link href="/login" className="block text-xs text-slate-400 hover:text-white transition-colors">
                                Already have an account? Sign In &rarr;
                            </Link>
                        </div>
                    </div>
                )}

                {/* 2. Active Card State */}
                {card.status === 'active' && (
                    <div className="bg-[#141A2E] rounded-3xl p-8 border border-slate-800 shadow-2xl text-center space-y-6 relative overflow-hidden">
                        <div className="w-20 h-20 rounded-full bg-gradient-to-tr from-[#6C5CE7] to-[#14B8A6] p-1 mx-auto shadow-xl shadow-[#6C5CE7]/30">
                            <div className="w-full h-full rounded-full bg-[#141A2E] flex items-center justify-center text-white font-extrabold text-2xl">
                                {owner?.name ? owner.name.charAt(0).toUpperCase() : 'A'}
                            </div>
                        </div>

                        <div className="space-y-1">
                            <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400 text-xs font-bold border border-emerald-500/20">
                                <CheckCircle2 className="w-3.5 h-3.5" />
                                <span>Verified Active Tag</span>
                            </div>
                            <h1 className="text-2xl font-extrabold text-white tracking-tight pt-1">
                                {owner?.name || 'AURA CARD Owner'}
                            </h1>
                            <p className="text-xs text-slate-400 font-mono">
                                Serial: {card.serial_number}
                            </p>
                        </div>

                        {/* Phase 3 Profile Teaser */}
                        <div className="p-4 rounded-2xl bg-[#1E2642]/60 border border-slate-700/60 text-xs text-slate-300 space-y-2 text-start">
                            <div className="flex items-center gap-2 text-white font-bold">
                                <Sparkles className="w-4 h-4 text-[#14B8A6]" />
                                <span>Digital Profile Ready</span>
                            </div>
                            <p className="text-slate-400 text-[11px] leading-relaxed">
                                Phase 2 physical NFC claiming is complete. The modular 12-block profile builder with one-click vCard download and social networks is rolling out in Phase 3.
                            </p>
                        </div>

                        <div className="pt-2">
                            <Link href="/dashboard" className="block">
                                <Button variant="secondary" className="w-full text-xs">
                                    Customer Portal
                                </Button>
                            </Link>
                        </div>
                    </div>
                )}

                {/* 3. Suspended Card State */}
                {card.status === 'suspended' && (
                    <div className="bg-[#141A2E] rounded-3xl p-8 border border-amber-900/40 shadow-2xl text-center space-y-5">
                        <div className="w-16 h-16 rounded-2xl bg-amber-500/10 text-amber-400 flex items-center justify-center mx-auto border border-amber-500/20">
                            <Lock className="w-8 h-8" />
                        </div>

                        <div className="space-y-2">
                            <Badge variant="warning">Card Paused</Badge>
                            <h1 className="text-xl font-extrabold text-white">
                                Card Temporarily Suspended
                            </h1>
                            <p className="text-xs text-slate-400 max-w-xs mx-auto leading-relaxed">
                                The card owner has temporarily paused NFC profile routing for privacy or security reasons.
                            </p>
                        </div>
                    </div>
                )}

                {/* 4. Lost Card State */}
                {card.status === 'lost' && (
                    <div className="bg-[#141A2E] rounded-3xl p-8 border border-rose-900/40 shadow-2xl text-center space-y-5">
                        <div className="w-16 h-16 rounded-2xl bg-rose-500/10 text-rose-400 flex items-center justify-center mx-auto border border-rose-500/20">
                            <ShieldAlert className="w-8 h-8" />
                        </div>

                        <div className="space-y-2">
                            <Badge variant="danger">Reported Lost</Badge>
                            <h1 className="text-xl font-extrabold text-white">
                                Card Inactive
                            </h1>
                            <p className="text-xs text-slate-400 max-w-xs mx-auto leading-relaxed">
                                This physical card was reported lost and has been safely deactivated from the network.
                            </p>
                        </div>
                    </div>
                )}

                {/* 5. Replaced or Disabled Card State */}
                {(card.status === 'replaced' || card.status === 'disabled') && (
                    <div className="bg-[#141A2E] rounded-3xl p-8 border border-slate-800 shadow-2xl text-center space-y-5">
                        <div className="w-16 h-16 rounded-2xl bg-slate-800 text-slate-400 flex items-center justify-center mx-auto">
                            <Smartphone className="w-8 h-8" />
                        </div>

                        <div className="space-y-2">
                            <Badge variant="neutral">Decommissioned</Badge>
                            <h1 className="text-xl font-extrabold text-white">
                                Card No Longer Active
                            </h1>
                            <p className="text-xs text-slate-400 max-w-xs mx-auto leading-relaxed">
                                This physical card is no longer in service.
                            </p>
                        </div>
                    </div>
                )}
            </main>

            {/* Footer */}
            <footer className="max-w-md mx-auto w-full text-center py-4 text-[11px] text-slate-500">
                <p>&copy; {new Date().getFullYear()} AURA CARD. Anti-Fraud NFC Smart Card Platform.</p>
            </footer>
        </div>
    );
}
