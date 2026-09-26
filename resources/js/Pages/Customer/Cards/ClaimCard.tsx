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

interface ClaimCardProps {
    initialToken?: string;
}

export default function ClaimCard({ initialToken = '' }: ClaimCardProps) {
    const { t } = useTranslation();

    const { data, setData, post, processing, errors } = useForm({
        raw_token: initialToken,
        claim_code: '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/cards/claim');
    };

    return (
        <AppLayout title={t('claim_card', 'Activate AURA CARD')}>
            <Head title={t('claim_card', 'Activate AURA CARD')} />

            <div className="max-w-2xl mx-auto space-y-8">
                {/* Back button */}
                <Link
                    href="/cards"
                    className="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-slate-900 dark:hover:text-white transition-colors"
                >
                    <ArrowLeft className="w-4 h-4" />
                    <span>Back to My NFC Cards</span>
                </Link>

                {/* Main Card */}
                <Card className="p-8 sm:p-10 space-y-8">
                    <div className="text-center space-y-3">
                        <div className="w-16 h-16 rounded-3xl bg-gradient-to-tr from-[#6C5CE7] to-[#14B8A6] text-white flex items-center justify-center mx-auto shadow-xl shadow-[#6C5CE7]/20">
                            <Sparkles className="w-8 h-8" />
                        </div>
                        <h2 className="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                            Link Your Physical AURA CARD
                        </h2>
                        <p className="text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                            Enter the secure activation code printed inside your physical card packaging.
                        </p>
                    </div>

                    {/* Anti-Fraud Notice */}
                    <div className="p-4 rounded-2xl bg-[#6C5CE7]/10 border border-[#6C5CE7]/20 flex items-start gap-3.5">
                        <ShieldCheck className="w-5 h-5 text-[#6C5CE7] shrink-0 mt-0.5" />
                        <div className="space-y-1 text-xs text-slate-600 dark:text-slate-300">
                            <p className="font-bold text-slate-900 dark:text-white">
                                One Physical Purchase = One Free Entitlement
                            </p>
                            <p>
                                Linking this card creates your hardware entitlement. Your <strong>2 free publication credits</strong> are preserved and will unlock automatically upon your first profile publication.
                            </p>
                        </div>
                    </div>

                    {/* Form */}
                    <form onSubmit={handleSubmit} className="space-y-6">
                        {/* Token Input (Hidden or Editable if not from tap) */}
                        <div className="space-y-2">
                            <Input
                                label="Card Security Token"
                                placeholder="Enter public token from NFC tap"
                                value={data.raw_token}
                                onChange={(e) => setData('raw_token', e.target.value)}
                                error={errors.raw_token}
                                required
                                className="font-mono text-xs"
                            />
                            <p className="text-[11px] text-slate-400">
                                Pre-filled automatically when tapping your physical card on your phone.
                            </p>
                        </div>

                        {/* Claim Code Input */}
                        <div className="space-y-2">
                            <Input
                                label="One-Time Claim Code"
                                placeholder="e.g. AURA-XXXX-XXXX"
                                value={data.claim_code}
                                onChange={(e) => setData('claim_code', e.target.value.toUpperCase())}
                                error={errors.claim_code}
                                required
                                className="font-mono text-center tracking-widest text-lg font-bold uppercase"
                            />
                            <p className="text-[11px] text-slate-400 text-center flex items-center justify-center gap-1">
                                <HelpCircle className="w-3.5 h-3.5" />
                                Printed inside the activation sleeve of your physical card
                            </p>
                        </div>

                        <div className="pt-2">
                            <Button
                                type="submit"
                                className="w-full h-12 text-base font-bold shadow-lg shadow-[#6C5CE7]/25"
                                isLoading={processing}
                                disabled={processing || !data.claim_code || !data.raw_token}
                            >
                                <CreditCard className="w-5 h-5 me-2" />
                                <span>Verify & Link Card</span>
                            </Button>
                        </div>
                    </form>

                    <div className="border-t border-slate-100 dark:border-slate-800 pt-6 space-y-3">
                        <span className="text-xs font-bold uppercase tracking-wider text-slate-400 block">
                            Hardware Activation Safeguards
                        </span>
                        <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs text-slate-500">
                            <div className="flex items-start gap-2">
                                <CheckCircle2 className="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" />
                                <span>One-time claim code consumption</span>
                            </div>
                            <div className="flex items-start gap-2">
                                <CheckCircle2 className="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" />
                                <span>Zero-knowledge public token hashing</span>
                            </div>
                            <div className="flex items-start gap-2">
                                <CheckCircle2 className="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" />
                                <span>Instant ownership verification</span>
                            </div>
                        </div>
                    </div>
                </Card>
            </div>
        </AppLayout>
    );
}
