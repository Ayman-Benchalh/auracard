import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { AppLayout } from '@/Layouts/AppLayout';
import { Card } from '@/Components/UI/Card';
import { Button } from '@/Components/UI/Button';
import { useTranslation } from '@/Hooks/useTranslation';
import {
    CheckCircle2,
    Sparkles,
    Smartphone,
    ArrowRight,
    ShieldCheck,
    Layers,
} from 'lucide-react';

interface ClaimSuccessProps {
    serialNumber?: string | null;
}

export default function ClaimSuccess({ serialNumber }: ClaimSuccessProps) {
    const { t } = useTranslation();

    return (
        <AppLayout title={t('claim_success', 'Card Activated Successfully')}>
            <Head title={t('claim_success', 'Card Activated Successfully')} />

            <div className="max-w-2xl mx-auto py-6 space-y-8">
                <Card className="p-8 sm:p-12 text-center space-y-8">
                    {/* Animated Success Badge */}
                    <div className="relative inline-flex mx-auto">
                        <div className="w-24 h-24 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 flex items-center justify-center mx-auto shadow-xl shadow-emerald-500/10">
                            <CheckCircle2 className="w-12 h-12" />
                        </div>
                        <div className="absolute -top-1 -end-1 w-8 h-8 rounded-full bg-gradient-to-tr from-[#6C5CE7] to-[#14B8A6] text-white flex items-center justify-center shadow-md">
                            <Sparkles className="w-4 h-4" />
                        </div>
                    </div>

                    {/* Headline */}
                    <div className="space-y-3">
                        <h2 className="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                            AURA CARD Linked Successfully!
                        </h2>
                        {serialNumber && (
                            <div className="inline-block px-4 py-1.5 rounded-full bg-slate-100 dark:bg-[#1E2642] text-xs font-mono font-bold text-slate-700 dark:text-slate-300">
                                Card Serial: {serialNumber}
                            </div>
                        )}
                        <p className="text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto leading-relaxed">
                            Your physical NFC card is now tied to your account and verified in the AURA CARD hardware network.
                        </p>
                    </div>

                    {/* Feature Clarity Grid */}
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 text-start text-xs">
                        <div className="p-4 rounded-2xl bg-slate-50 dark:bg-[#1E2642]/60 border border-slate-200/80 dark:border-slate-800 space-y-2">
                            <div className="flex items-center gap-2 text-slate-900 dark:text-white font-bold">
                                <Smartphone className="w-4 h-4 text-[#14B8A6]" />
                                <span>Zero-App Visitor Access</span>
                            </div>
                            <p className="text-slate-500 leading-relaxed">
                                Anyone who taps your card with an NFC-enabled smartphone will instantly view your digital profile in their web browser.
                            </p>
                        </div>

                        <div className="p-4 rounded-2xl bg-slate-50 dark:bg-[#1E2642]/60 border border-slate-200/80 dark:border-slate-800 space-y-2">
                            <div className="flex items-center gap-2 text-slate-900 dark:text-white font-bold">
                                <ShieldCheck className="w-4 h-4 text-[#6C5CE7]" />
                                <span>2 Free Publication Credits</span>
                            </div>
                            <p className="text-slate-500 leading-relaxed">
                                Your physical entitlement is secured. Your 2 free publication credits will be automatically credited upon your first profile publication.
                            </p>
                        </div>
                    </div>

                    {/* Next Steps Banner (Phase 3 Teaser) */}
                    <div className="p-4 rounded-2xl bg-gradient-to-r from-[#6C5CE7]/10 to-[#14B8A6]/10 border border-[#6C5CE7]/20 text-xs text-slate-600 dark:text-slate-300 text-start flex items-center justify-between gap-4">
                        <div className="space-y-0.5">
                            <span className="font-bold text-slate-900 dark:text-white block flex items-center gap-1.5">
                                <Layers className="w-4 h-4 text-[#6C5CE7]" />
                                Next: Digital Profile Builder
                            </span>
                            <span className="text-[11px] text-slate-500">
                                The 12 modular blocks, vCard 4.0 download, and social links are prepared for setup in Phase 3.
                            </span>
                        </div>
                    </div>

                    {/* Action buttons */}
                    <div className="flex flex-col sm:flex-row items-center justify-center gap-4 pt-2">
                        <Link href="/cards" className="w-full sm:w-auto">
                            <Button className="w-full sm:w-auto inline-flex items-center justify-center gap-2">
                                <span>Manage My NFC Cards</span>
                                <ArrowRight className="w-4 h-4" />
                            </Button>
                        </Link>
                        <Link href="/dashboard" className="w-full sm:w-auto">
                            <Button variant="secondary" className="w-full sm:w-auto">
                                <span>Return to Dashboard</span>
                            </Button>
                        </Link>
                    </div>
                </Card>
            </div>
        </AppLayout>
    );
}
