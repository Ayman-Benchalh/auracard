import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { AppLayout } from '@/Layouts/AppLayout';
import { Card } from '@/Components/UI/Card';
import { Button } from '@/Components/UI/Button';
import { Input } from '@/Components/UI/Input';
import { useTranslation } from '@/Hooks/useTranslation';
import {
    Sparkles,
    ShieldCheck,
    CreditCard,
    ArrowLeft,
    CheckCircle2,
    HelpCircle,
} from 'lucide-react';

interface ClaimProps {
    initialToken?: string;
}

export default function Claim({ initialToken = '' }: ClaimProps) {
    const { t } = useTranslation();

    const { data, setData, post, processing, errors } = useForm({
        claim_token: initialToken,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/cards/claim');
    };

    return (
        <AppLayout title={t('claim_card', 'Activate NFC Card')}>
            <Head title={t('claim_card', 'Activate NFC Card')} />

            <div className="max-w-2xl mx-auto space-y-8">
                {/* Back button */}
                <Link
                    href="/cards"
                    className="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-slate-900 dark:hover:text-white transition-colors"
                >
                    <ArrowLeft className="w-4 h-4" />
                    <span>Back to My Cards</span>
                </Link>

                {/* Main Activation Card */}
                <Card className="p-8 sm:p-10 space-y-8">
                    {/* Header */}
                    <div className="text-center space-y-3">
                        <div className="w-16 h-16 rounded-3xl bg-gradient-to-tr from-[#6C5CE7] to-[#14B8A6] text-white flex items-center justify-center mx-auto shadow-xl shadow-[#6C5CE7]/20">
                            <Sparkles className="w-8 h-8" />
                        </div>
                        <h2 className="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                            Link Your Physical AURA CARD
                        </h2>
                        <p className="text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                            Enter the unique cryptographic activation code printed inside your physical card's packaging sleeve.
                        </p>
                    </div>

                    {/* Anti-Fraud Entitlement Highlight Callout */}
                    <div className="p-4 rounded-2xl bg-[#6C5CE7]/10 border border-[#6C5CE7]/20 flex items-start gap-3.5">
                        <ShieldCheck className="w-5 h-5 text-[#6C5CE7] shrink-0 mt-0.5" />
                        <div className="space-y-1 text-xs text-slate-600 dark:text-slate-300">
                            <p className="font-bold text-slate-900 dark:text-white">
                                Card-Bound Anti-Fraud Guarantee
                            </p>
                            <p>
                                Activating this card assigns your digital profile and unlocks <strong>2 free publication credits</strong> directly bound to this physical hardware unit.
                            </p>
                        </div>
                    </div>

                    {/* Activation Form */}
                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div className="space-y-2">
                            <Input
                                label="Activation Code / Claim Token"
                                placeholder="e.g. AURA-XXXX-XXXX-XXXX"
                                value={data.claim_token}
                                onChange={(e) => setData('claim_token', e.target.value.toUpperCase())}
                                error={errors.claim_token}
                                required
                                className="font-mono text-center tracking-widest text-lg font-bold uppercase"
                            />
                            <p className="text-[11px] text-slate-400 text-center flex items-center justify-center gap-1">
                                <HelpCircle className="w-3.5 h-3.5" />
                                Code format: AURA followed by 12 characters
                            </p>
                        </div>

                        <div className="pt-2">
                            <Button
                                type="submit"
                                className="w-full h-12 text-base font-bold shadow-lg shadow-[#6C5CE7]/25"
                                isLoading={processing}
                                disabled={processing || !data.claim_token}
                            >
                                <CreditCard className="w-5 h-5 me-2" />
                                <span>Activate & Link Card</span>
                            </Button>
                        </div>
                    </form>

                    {/* Steps Helper */}
                    <div className="border-t border-slate-100 dark:border-slate-800 pt-6 space-y-3">
                        <span className="text-xs font-bold uppercase tracking-wider text-slate-400 block">
                            How Activation Works
                        </span>
                        <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs text-slate-500">
                            <div className="flex items-start gap-2">
                                <CheckCircle2 className="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" />
                                <span>1. Redeem code on packaging</span>
                            </div>
                            <div className="flex items-start gap-2">
                                <CheckCircle2 className="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" />
                                <span>2. Card links instantly to your profile</span>
                            </div>
                            <div className="flex items-start gap-2">
                                <CheckCircle2 className="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" />
                                <span>3. Tap on any smartphone to share</span>
                            </div>
                        </div>
                    </div>
                </Card>
            </div>
        </AppLayout>
    );
}
