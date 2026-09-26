import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { AppLayout } from '@/Layouts/AppLayout';
import { Card } from '@/Components/UI/Card';
import { Button } from '@/Components/UI/Button';
import { Input } from '@/Components/UI/Input';
import { Badge } from '@/Components/UI/Badge';
import {
    CreditCard,
    Building2,
    Banknote,
    UploadCloud,
    FileCheck,
    CheckCircle2,
    AlertCircle,
    Copy,
    Check,
    ArrowLeft,
    ShieldCheck
} from 'lucide-react';

interface PaymentSetting {
    id: string;
    key: string;
    title: string;
    instructions?: string;
    bank_name?: string;
    rib?: string;
    swift_bic?: string;
    account_holder?: string;
}

interface Plan {
    id: string;
    key: string;
    name: string;
    description: string;
    price: number;
    currency: string;
    billing_period: string;
}

interface Props {
    plan: Plan;
    paymentSettings: PaymentSetting[];
    userReferenceHint: string;
}

export default function Checkout({ plan, paymentSettings, userReferenceHint }: Props) {
    const [selectedMethod, setSelectedMethod] = useState<string>('bank_transfer');
    const [copiedField, setCopiedField] = useState<string | null>(null);

    const { data, setData, post, processing, errors } = useForm<{
        payment_method: string;
        reference_number: string;
        customer_notes: string;
        proof: File | null;
    }>({
        payment_method: 'bank_transfer',
        reference_number: '',
        customer_notes: '',
        proof: null,
    });

    const handleCopy = (text: string, field: string) => {
        navigator.clipboard.writeText(text);
        setCopiedField(field);
        setTimeout(() => setCopiedField(null), 2000);
    };

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        if (e.target.files && e.target.files[0]) {
            setData('proof', e.target.files[0]);
        }
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(`/checkout/${plan.id}`);
    };

    const activeBankSetting = paymentSettings.find(s => s.key === 'manual_bank_transfer');
    const activeCashSetting = paymentSettings.find(s => s.key === 'cash_deposit');

    return (
        <AppLayout title={`Paiement - ${plan.name}`}>
            <Head title={`Paiement Forfait ${plan.name}`} />

            <div className="max-w-5xl mx-auto space-y-8 py-6 px-4">
                {/* Back Link */}
                <div>
                    <Link
                        href="/pricing"
                        className="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-slate-900 dark:hover:text-white transition-colors"
                    >
                        <ArrowLeft className="w-4 h-4" />
                        Retourner aux forfaits
                    </Link>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    {/* Left: Payment Form & Instructions */}
                    <div className="lg:col-span-7 space-y-6">
                        {/* 1. Payment Method Selection */}
                        <Card className="space-y-4">
                            <h2 className="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <span className="w-6 h-6 rounded-full bg-[#6C5CE7] text-white flex items-center justify-center text-xs font-bold">1</span>
                                Choisissez votre mode de règlement
                            </h2>

                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <button
                                    type="button"
                                    onClick={() => {
                                        setSelectedMethod('bank_transfer');
                                        setData('payment_method', 'bank_transfer');
                                    }}
                                    className={`p-4 rounded-2xl border text-left flex flex-col justify-between transition-all cursor-pointer ${
                                        selectedMethod === 'bank_transfer'
                                            ? 'border-[#6C5CE7] bg-[#6C5CE7]/5 dark:bg-[#6C5CE7]/10 ring-2 ring-[#6C5CE7]/30'
                                            : 'border-slate-200 dark:border-slate-800 hover:border-slate-300'
                                    }`}
                                >
                                    <div className="flex items-center justify-between w-full">
                                        <Building2 className={`w-6 h-6 ${selectedMethod === 'bank_transfer' ? 'text-[#6C5CE7]' : 'text-slate-400'}`} />
                                        {selectedMethod === 'bank_transfer' && <CheckCircle2 className="w-4 h-4 text-[#6C5CE7]" />}
                                    </div>
                                    <div className="mt-3">
                                        <p className="font-bold text-sm text-slate-900 dark:text-white">Virement Bancaire</p>
                                        <p className="text-xs text-slate-500">RIB Attijariwafa / CIH / BMCE</p>
                                    </div>
                                </button>

                                <button
                                    type="button"
                                    onClick={() => {
                                        setSelectedMethod('cash_deposit');
                                        setData('payment_method', 'cash_deposit');
                                    }}
                                    className={`p-4 rounded-2xl border text-left flex flex-col justify-between transition-all cursor-pointer ${
                                        selectedMethod === 'cash_deposit'
                                            ? 'border-[#6C5CE7] bg-[#6C5CE7]/5 dark:bg-[#6C5CE7]/10 ring-2 ring-[#6C5CE7]/30'
                                            : 'border-slate-200 dark:border-slate-800 hover:border-slate-300'
                                    }`}
                                >
                                    <div className="flex items-center justify-between w-full">
                                        <Banknote className={`w-6 h-6 ${selectedMethod === 'cash_deposit' ? 'text-[#6C5CE7]' : 'text-slate-400'}`} />
                                        {selectedMethod === 'cash_deposit' && <CheckCircle2 className="w-4 h-4 text-[#6C5CE7]" />}
                                    </div>
                                    <div className="mt-3">
                                        <p className="font-bold text-sm text-slate-900 dark:text-white">Versement Espèces</p>
                                        <p className="text-xs text-slate-500">Réseaux d'agences et guichets</p>
                                    </div>
                                </button>
                            </div>
                        </Card>

                        {/* 2. Bank / Agency Coordinates */}
                        <Card className="space-y-4">
                            <h2 className="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <span className="w-6 h-6 rounded-full bg-[#6C5CE7] text-white flex items-center justify-center text-xs font-bold">2</span>
                                Effectuez le règlement
                            </h2>

                            {selectedMethod === 'bank_transfer' && activeBankSetting ? (
                                <div className="space-y-4 bg-slate-50 dark:bg-slate-900/50 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800">
                                    <div className="flex items-center justify-between">
                                        <span className="text-xs font-bold uppercase tracking-wider text-slate-500">Banque</span>
                                        <span className="text-sm font-bold text-slate-900 dark:text-white">{activeBankSetting.bank_name || 'Banque Partenaire Démo'}</span>
                                    </div>

                                    <div className="flex items-center justify-between">
                                        <span className="text-xs font-bold uppercase tracking-wider text-slate-500">Titulaire</span>
                                        <span className="text-sm font-semibold text-slate-900 dark:text-white">{activeBankSetting.account_holder || 'AURA CARD SARL DÉMO'}</span>
                                    </div>

                                    <div className="space-y-1">
                                        <span className="text-xs font-bold uppercase tracking-wider text-slate-500">RIB (24 chiffres)</span>
                                        <div className="flex items-center justify-between gap-2 bg-white dark:bg-[#141A2E] p-3 rounded-xl border border-slate-200 dark:border-slate-700 font-mono text-xs sm:text-sm font-bold text-slate-900 dark:text-white select-all">
                                            <span>{activeBankSetting.rib || '000 000 0000000000000000 00'}</span>
                                            <button
                                                type="button"
                                                onClick={() => handleCopy(activeBankSetting.rib || '000000000000000000000000', 'rib')}
                                                className="p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 transition-colors"
                                                title="Copier le RIB"
                                            >
                                                {copiedField === 'rib' ? <Check className="w-4 h-4 text-emerald-500" /> : <Copy className="w-4 h-4" />}
                                            </button>
                                        </div>
                                    </div>

                                    {activeBankSetting.swift_bic && (
                                        <div className="flex items-center justify-between text-xs text-slate-500">
                                            <span>Code SWIFT / BIC</span>
                                            <span className="font-mono font-medium text-slate-700 dark:text-slate-300">{activeBankSetting.swift_bic}</span>
                                        </div>
                                    )}

                                    <div className="mt-4 p-3 bg-amber-50 dark:bg-amber-950/30 rounded-xl border border-amber-200 dark:border-amber-800/50 text-xs text-amber-800 dark:text-amber-300 flex items-start gap-2">
                                        <AlertCircle className="w-4 h-4 shrink-0 mt-0.5" />
                                        <span>
                                            <strong>Motif du virement obligatoire :</strong> Veuillez indiquer votre référence client{' '}
                                            <code className="px-1.5 py-0.5 bg-amber-100 dark:bg-amber-900/50 font-mono font-bold rounded">{userReferenceHint}</code>{' '}
                                            dans le libellé du virement pour accélérer la validation.
                                        </span>
                                    </div>
                                </div>
                            ) : (
                                <div className="space-y-4 bg-slate-50 dark:bg-slate-900/50 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800">
                                    <p className="text-sm text-slate-700 dark:text-slate-300">
                                        {activeCashSetting?.instructions || 'Rendez-vous dans une agence de paiement agréée pour effectuer un versement en espèces direct sur le compte AURA CARD.'}
                                    </p>
                                    <div className="p-3 bg-amber-50 dark:bg-amber-950/30 rounded-xl border border-amber-200 dark:border-amber-800/50 text-xs text-amber-800 dark:text-amber-300 flex items-start gap-2">
                                        <AlertCircle className="w-4 h-4 shrink-0 mt-0.5" />
                                        <span>Conservez soigneusement le reçu papier délivré par l\'agence pour le téléverser ci-dessous.</span>
                                    </div>
                                </div>
                            )}
                        </Card>

                        {/* 3. Proof Upload & Confirmation */}
                        <Card className="space-y-5">
                            <h2 className="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                <span className="w-6 h-6 rounded-full bg-[#6C5CE7] text-white flex items-center justify-center text-xs font-bold">3</span>
                                Déposez votre reçu de paiement
                            </h2>

                            <form onSubmit={handleSubmit} className="space-y-5">
                                <div>
                                    <label className="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                        Numéro de référence ou reçu (facultatif)
                                    </label>
                                    <Input
                                        type="text"
                                        placeholder="Ex: VIR-2026-98421 ou Ref Agence"
                                        value={data.reference_number}
                                        onChange={(e) => setData('reference_number', e.target.value)}
                                        error={errors.reference_number}
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                        Fichier du reçu (PDF, PNG, JPG, WebP - Max 10 Mo) *
                                    </label>
                                    <div className="border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl p-6 text-center hover:border-[#6C5CE7] transition-colors relative">
                                        <input
                                            type="file"
                                            accept=".pdf,image/png,image/jpeg,image/webp"
                                            onChange={handleFileChange}
                                            className="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                            id="payment-proof-upload"
                                        />
                                        <div className="flex flex-col items-center justify-center space-y-2 pointer-events-none">
                                            {data.proof ? (
                                                <div className="flex items-center gap-2 text-emerald-600 dark:text-emerald-400 font-medium text-sm">
                                                    <FileCheck className="w-6 h-6" />
                                                    <span>{data.proof.name}</span>
                                                </div>
                                            ) : (
                                                <>
                                                    <UploadCloud className="w-8 h-8 text-slate-400" />
                                                    <p className="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                                        Cliquez ou glissez votre reçu ici
                                                    </p>
                                                    <p className="text-xs text-slate-400">
                                                        Formats acceptés : PDF, JPG, PNG, WebP (Vérification stricte anti-malware)
                                                    </p>
                                                </>
                                            )}
                                        </div>
                                    </div>
                                    {errors.proof && (
                                        <p className="mt-1 text-xs text-rose-500">{errors.proof}</p>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
                                        Remarques particulières (facultatif)
                                    </label>
                                    <textarea
                                        rows={2}
                                        className="w-full px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#141A2E] text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#6C5CE7]"
                                        placeholder="Ex: Virement émis depuis compte bancaire société..."
                                        value={data.customer_notes}
                                        onChange={(e) => setData('customer_notes', e.target.value)}
                                    />
                                    {errors.customer_notes && (
                                        <p className="mt-1 text-xs text-rose-500">{errors.customer_notes}</p>
                                    )}
                                </div>

                                <div className="pt-2">
                                    <Button
                                        type="submit"
                                        variant="primary"
                                        size="lg"
                                        className="w-full text-base font-bold shadow-lg shadow-[#6C5CE7]/30"
                                        isLoading={processing}
                                        disabled={!data.proof || processing}
                                    >
                                        Confirmer & Soumettre mon paiement
                                    </Button>
                                </div>

                                <div className="flex items-center justify-center gap-2 text-xs text-slate-400">
                                    <ShieldCheck className="w-4 h-4 text-emerald-500" />
                                    <span>Stockage sécurisé confidentiel • Traitement sous 2 à 4h ouvrées</span>
                                </div>
                            </form>
                        </Card>
                    </div>

                    {/* Right: Order Summary */}
                    <div className="lg:col-span-5 sticky top-24">
                        <Card className="p-6 space-y-6 bg-gradient-to-b from-white to-slate-50 dark:from-[#141A2E] dark:to-[#0F1424] border border-slate-200 dark:border-slate-800 shadow-xl">
                            <h3 className="text-base font-bold text-slate-900 dark:text-white uppercase tracking-wider text-xs border-b border-slate-100 dark:border-slate-800 pb-3">
                                Récapitulatif de commande
                            </h3>

                            <div className="space-y-4">
                                <div className="flex justify-between items-start">
                                    <div>
                                        <h4 className="font-bold text-lg text-slate-900 dark:text-white">{plan.name}</h4>
                                        <p className="text-xs text-slate-500">{plan.description}</p>
                                    </div>
                                    <Badge variant="primary">Annuel</Badge>
                                </div>

                                <div className="border-t border-b border-slate-100 dark:border-slate-800/80 py-4 space-y-2">
                                    <div className="flex justify-between text-sm text-slate-600 dark:text-slate-400">
                                        <span>Tarif forfait (12 mois)</span>
                                        <span>{plan.price} {plan.currency}</span>
                                    </div>
                                    <div className="flex justify-between text-sm text-slate-600 dark:text-slate-400">
                                        <span>Frais d'activation</span>
                                        <span className="text-emerald-600 dark:text-emerald-400 font-semibold">Gratuit (0 MAD)</span>
                                    </div>
                                </div>

                                <div className="flex justify-between items-baseline pt-2">
                                    <span className="text-base font-bold text-slate-900 dark:text-white">Total à régler</span>
                                    <div className="text-right">
                                        <span className="text-3xl font-black text-[#6C5CE7]">{plan.price}</span>
                                        <span className="text-sm font-bold text-slate-600 dark:text-slate-400 ml-1">{plan.currency}</span>
                                        <p className="text-[11px] text-slate-400">Toutes taxes comprises</p>
                                    </div>
                                </div>
                            </div>

                            <div className="p-4 rounded-xl bg-slate-100 dark:bg-slate-800/60 text-xs text-slate-600 dark:text-slate-400 space-y-2">
                                <p className="font-semibold text-slate-800 dark:text-slate-200">Garanties AURA CARD :</p>
                                <ul className="list-disc list-inside space-y-1">
                                    <li>Accès immédiat après validation du reçu</li>
                                    <li>Reçu fiscal officiel téléchargeable</li>
                                    <li>Support WhatsApp & Email dédié</li>
                                </ul>
                            </div>
                        </Card>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
