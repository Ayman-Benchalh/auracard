import React, { useState } from 'react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
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
    ShieldCheck,
    Palette,
    Layers,
    Sparkles,
    Smartphone,
    Info,
    CheckCircle
} from 'lucide-react';
import { PageProps } from '@/types';

interface CardDesign {
    id: string;
    key: string;
    name: Record<string, string> | string;
    description: Record<string, string> | string;
    material: string;
    color: string;
    preview_image?: string;
}

interface CommercialPackage {
    id: string;
    key: string;
    name: Record<string, string> | string;
    description: Record<string, string> | string;
    price: number;
    currency: string;
    duration_months: number;
    physical_cards_included: number;
    profiles_included: number;
    edit_credits_per_profile: number;
    allows_customer_owned_card: boolean;
    requires_customer_owned_card: boolean;
    features_list?: Record<string, string[]> | null;
    is_featured: boolean;
}

interface PaymentSetting {
    id: string;
    key: string;
    title: Record<string, string> | string;
    instructions?: Record<string, string> | string;
    bank_name?: string;
    rib?: string;
    swift_bic?: string;
    account_holder?: string;
}

interface Props {
    package: CommercialPackage;
    cardDesigns: CardDesign[];
    paymentSettings: PaymentSetting[];
    userReferenceHint: string;
}

export default function PackageCheckout({
    package: pkg,
    cardDesigns,
    paymentSettings,
    userReferenceHint,
}: Props) {
    const { locale = 'fr' } = usePage<PageProps>().props;

    const getLocalized = (field: any, defaultStr: string = '') => {
        if (!field) return defaultStr;
        if (typeof field === 'string') return field;
        return field[locale] || field['fr'] || field['en'] || defaultStr;
    };

    const initialSelections = Array(pkg.physical_cards_included).fill(
        cardDesigns[0]?.key || 'matte_black'
    );

    const defaultPaymentMethod = paymentSettings[0]?.key || 'manual_bank_transfer';
    const [cardSelections, setCardSelections] = useState<string[]>(initialSelections);
    const [selectedMethod, setSelectedMethod] = useState<string>(defaultPaymentMethod);
    const [copiedField, setCopiedField] = useState<string | null>(null);
    const [previewCardIndex, setPreviewCardIndex] = useState<number>(0);

    const { data, setData, post, processing, errors } = useForm<{
        card_selections: string[];
        external_card_data: { tag_type: string; notes: string };
        payment_method: string;
        reference_number: string;
        customer_notes: string;
        proof_file: File | null;
    }>({
        card_selections: initialSelections,
        external_card_data: { tag_type: 'NTAG215', notes: '' },
        payment_method: defaultPaymentMethod,
        reference_number: '',
        customer_notes: '',
        proof_file: null,
    });

    const handleDesignChange = (cardIndex: number, designKey: string) => {
        const next = [...cardSelections];
        next[cardIndex] = designKey;
        setCardSelections(next);
        setData('card_selections', next);
    };

    const handleApplyToAll = (designKey: string) => {
        const next = Array(pkg.physical_cards_included).fill(designKey);
        setCardSelections(next);
        setData('card_selections', next);
    };

    const handleCopy = (text: string, fieldKey: string) => {
        navigator.clipboard.writeText(text);
        setCopiedField(fieldKey);
        setTimeout(() => setCopiedField(null), 2500);
    };

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        if (e.target.files && e.target.files[0]) {
            setData('proof_file', e.target.files[0]);
        }
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(`/checkout/package/${pkg.id}`);
    };

    const activeBankSetting = paymentSettings.find(
        (s) => s.key === 'manual_bank_transfer'
    ) || paymentSettings[0];
    const activeCashSetting = paymentSettings.find(
        (s) => s.key === 'cash_deposit'
    );

    const currentDesign = cardDesigns.find(
        (d) => d.key === cardSelections[previewCardIndex]
    ) || cardDesigns[0];

    const getCardStyle = (designKey: string) => {
        switch (designKey) {
            case 'metal_gold':
                return 'bg-gradient-to-tr from-[#92400E] via-[#D97706] to-[#FDE68A] text-slate-900 border-[#B45309] shadow-[#D97706]/30';
            case 'bamboo_wood':
                return 'bg-gradient-to-tr from-[#451A03] via-[#78350F] to-[#B45309] text-amber-50 border-[#92400E] shadow-[#78350F]/30';
            case 'aura_gradient':
                return 'bg-gradient-to-tr from-[#4F46E5] via-[#7C3AED] to-[#06B6D4] text-white border-[#818CF8] shadow-[#7C3AED]/40';
            case 'matte_black':
            default:
                return 'bg-gradient-to-tr from-[#0F172A] via-[#1E293B] to-[#334155] text-slate-100 border-slate-700 shadow-black/50';
        }
    };

    return (
        <AppLayout title={`Commande - ${getLocalized(pkg.name)}`}>
            <Head title={`Commande ${getLocalized(pkg.name)}`} />

            <div className="max-w-6xl mx-auto space-y-8 py-8 px-4 sm:px-6">
                {/* Back button & Breadcrumbs */}
                <div className="flex items-center justify-between">
                    <Link
                        href="/pricing"
                        className="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-slate-900 dark:hover:text-white transition-colors"
                    >
                        <ArrowLeft className="w-4 h-4" />
                        Retourner aux forfaits
                    </Link>

                    <Badge variant="primary" className="px-3 py-1 text-xs uppercase font-bold">
                        Paiement Sécurisé • Maroc
                    </Badge>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    {/* LEFT COLUMN: Configuration & Payment Details */}
                    <div className="lg:col-span-7 space-y-8">
                        {/* STEP 1: Physical NFC Card Customization */}
                        {pkg.physical_cards_included > 0 ? (
                            <Card className="p-6 sm:p-8 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
                                <div className="flex items-center justify-between border-b border-slate-100 dark:border-slate-800/80 pb-4">
                                    <div className="flex items-center gap-3">
                                        <div className="w-9 h-9 rounded-xl bg-[#6C5CE7]/10 text-[#6C5CE7] flex items-center justify-center font-bold text-sm">
                                            1
                                        </div>
                                        <div>
                                            <h2 className="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                                <Palette className="w-4 h-4 text-[#6C5CE7]" />
                                                Personnalisation des Cartes NFC
                                            </h2>
                                            <p className="text-xs text-slate-500">
                                                Choisissez la finition et le matériau pour chaque carte incluse dans votre pack.
                                            </p>
                                        </div>
                                    </div>
                                    <Badge variant="neutral">
                                        {pkg.physical_cards_included} Carte{pkg.physical_cards_included > 1 ? 's' : ''}
                                    </Badge>
                                </div>

                                {/* Multi-card Selector for Trio / Business 5 */}
                                {pkg.physical_cards_included > 1 && (
                                    <div className="space-y-3">
                                        <div className="flex items-center justify-between">
                                            <label className="text-xs font-bold uppercase tracking-wider text-slate-500">
                                                Sélectionnez la carte à configurer :
                                            </label>
                                            <div className="flex items-center gap-1.5">
                                                {cardDesigns.map((d) => (
                                                    <button
                                                        key={d.key}
                                                        type="button"
                                                        onClick={() => handleApplyToAll(d.key)}
                                                        className="text-[11px] text-slate-500 hover:text-[#6C5CE7] hover:underline"
                                                    >
                                                        Tous en {getLocalized(d.name).split(' ')[0]}
                                                    </button>
                                                ))}
                                            </div>
                                        </div>
                                        <div className="grid grid-cols-3 sm:grid-cols-5 gap-2">
                                            {cardSelections.map((sel, idx) => {
                                                const d = cardDesigns.find((des) => des.key === sel);
                                                const isCurrent = previewCardIndex === idx;
                                                return (
                                                    <button
                                                        key={idx}
                                                        type="button"
                                                        onClick={() => setPreviewCardIndex(idx)}
                                                        className={`p-3 rounded-2xl text-left border transition-all ${
                                                            isCurrent
                                                                ? 'border-[#6C5CE7] bg-[#6C5CE7]/10 shadow-sm'
                                                                : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-[#141A2E] hover:border-slate-300'
                                                        }`}
                                                    >
                                                        <div className="text-xs font-bold text-slate-900 dark:text-white">
                                                            Carte #{idx + 1}
                                                        </div>
                                                        <div className="text-[11px] text-slate-500 truncate mt-0.5">
                                                            {d ? getLocalized(d.name).split(' ')[0] : sel}
                                                        </div>
                                                    </button>
                                                );
                                            })}
                                        </div>
                                    </div>
                                )}

                                {/* Available Designs Carousel / Cards */}
                                <div className="space-y-3">
                                    <label className="text-xs font-bold uppercase tracking-wider text-slate-500">
                                        Finitions & Matériaux disponibles :
                                    </label>
                                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                        {cardDesigns.map((design) => {
                                            const isSelected =
                                                cardSelections[previewCardIndex] === design.key;
                                            return (
                                                <button
                                                    key={design.key}
                                                    type="button"
                                                    onClick={() => {
                                                        handleDesignChange(previewCardIndex, design.key);
                                                    }}
                                                    className={`p-4 rounded-2xl border text-left flex items-start gap-3.5 transition-all duration-200 ${
                                                        isSelected
                                                            ? 'border-2 border-[#6C5CE7] bg-[#6C5CE7]/5 shadow-md shadow-[#6C5CE7]/10'
                                                            : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-[#141A2E] hover:border-slate-300 dark:hover:border-slate-700'
                                                    }`}
                                                >
                                                    <div
                                                        className="w-10 h-10 rounded-xl shrink-0 border border-black/10 flex items-center justify-center shadow-inner"
                                                        style={{ backgroundColor: design.color }}
                                                    >
                                                        {isSelected && (
                                                            <CheckCircle2 className="w-5 h-5 text-white drop-shadow" />
                                                        )}
                                                    </div>
                                                    <div className="min-w-0 flex-1">
                                                        <div className="flex items-center justify-between">
                                                            <div className="font-bold text-sm text-slate-900 dark:text-white">
                                                                {getLocalized(design.name)}
                                                            </div>
                                                            <span className="text-[10px] px-2 py-0.5 rounded-full font-semibold uppercase bg-slate-100 dark:bg-slate-800 text-slate-500">
                                                                {design.material.replace('_', ' ')}
                                                            </span>
                                                        </div>
                                                        <p className="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">
                                                            {getLocalized(design.description)}
                                                        </p>
                                                    </div>
                                                </button>
                                            );
                                        })}
                                    </div>
                                    {errors.card_selections && (
                                        <p className="text-xs text-rose-500 font-semibold mt-1">
                                            {errors.card_selections}
                                        </p>
                                    )}
                                </div>
                            </Card>
                        ) : (
                            <Card className="p-6 sm:p-8 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                                <div className="flex items-center gap-3 border-b border-slate-100 dark:border-slate-800/80 pb-4">
                                    <div className="w-9 h-9 rounded-xl bg-teal-500/10 text-teal-600 flex items-center justify-center font-bold text-sm">
                                        1
                                    </div>
                                    <div>
                                        <h2 className="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                            <Smartphone className="w-4 h-4 text-teal-600" />
                                            Offre Profil Digital Seul
                                        </h2>
                                        <p className="text-xs text-slate-500">
                                            Aucune carte physique n'est expédiée. Vous pouvez utiliser votre propre carte NFC existante.
                                        </p>
                                    </div>
                                </div>
                                <div className="p-4 rounded-2xl bg-teal-50 dark:bg-teal-950/20 border border-teal-200 dark:border-teal-800/40 text-xs text-teal-800 dark:text-teal-200 flex items-start gap-3">
                                    <Info className="w-4 h-4 shrink-0 mt-0.5" />
                                    <span>
                                        Dès activation de votre commande, une URL sécurisée et un QR code dynamique vous seront attribués pour encoder n'importe quel badge NFC compatible (NTAG213, NTAG215, NTAG216).
                                    </span>
                                </div>
                            </Card>
                        )}

                        {/* STEP 2: Moroccan Payment Rails */}
                        <Card className="p-6 sm:p-8 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
                            <div className="flex items-center gap-3 border-b border-slate-100 dark:border-slate-800/80 pb-4">
                                <div className="w-9 h-9 rounded-xl bg-[#6C5CE7]/10 text-[#6C5CE7] flex items-center justify-center font-bold text-sm">
                                    2
                                </div>
                                <div>
                                    <h2 className="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                        <Building2 className="w-4 h-4 text-[#6C5CE7]" />
                                        Instructions de Paiement National
                                    </h2>
                                    <p className="text-xs text-slate-500">
                                        Réglez par virement bancaire sécurisé ou versement espèces en agence au Maroc.
                                    </p>
                                </div>
                            </div>

                            {/* Payment Method Selector Tabs - Dynamically driven by database */}
                            {paymentSettings.length > 0 ? (
                                <>
                                    <div className="flex flex-wrap gap-2.5">
                                        {paymentSettings.map((setting) => {
                                            const isSelected = selectedMethod === setting.key;
                                            return (
                                                <button
                                                    key={setting.id}
                                                    type="button"
                                                    onClick={() => {
                                                        setSelectedMethod(setting.key);
                                                        setData('payment_method', setting.key);
                                                    }}
                                                    className={`px-4 py-3 rounded-2xl border text-left flex-1 min-w-[200px] transition-all ${
                                                        isSelected
                                                            ? 'border-2 border-[#6C5CE7] bg-[#6C5CE7]/10 text-slate-900 dark:text-white font-bold shadow-sm'
                                                            : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300'
                                                    }`}
                                                >
                                                    <div className="flex items-center gap-2 mb-1">
                                                        <Building2 className="w-4 h-4 text-[#6C5CE7]" />
                                                        <span className="text-sm font-bold">
                                                            {getLocalized(setting.title, setting.key)}
                                                        </span>
                                                    </div>
                                                    {setting.bank_name && (
                                                        <div className="text-[11px] text-slate-500 font-normal">
                                                            {getLocalized(setting.bank_name)}
                                                        </div>
                                                    )}
                                                </button>
                                            );
                                        })}
                                    </div>

                                    {/* Active Setting Details Panel */}
                                    {(() => {
                                        const currentSetting =
                                            paymentSettings.find((s) => s.key === selectedMethod) ||
                                            paymentSettings[0];
                                        if (!currentSetting) return null;

                                        return (
                                            <div className="p-5 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 space-y-4">
                                                <div className="flex items-center justify-between">
                                                    <span className="text-xs font-bold text-slate-500 uppercase tracking-wider">
                                                        {getLocalized(currentSetting.title)}
                                                    </span>
                                                    <Badge variant="primary">Instructions Officielles</Badge>
                                                </div>

                                                {/* Instructions text from DB */}
                                                {currentSetting.instructions && (
                                                    <p className="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                                                        {getLocalized(currentSetting.instructions)}
                                                    </p>
                                                )}

                                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                                    {currentSetting.bank_name && (
                                                        <div>
                                                            <span className="text-xs text-slate-400">Établissement / Opérateur</span>
                                                            <div className="font-bold text-slate-900 dark:text-white">
                                                                {getLocalized(currentSetting.bank_name)}
                                                            </div>
                                                        </div>
                                                    )}
                                                    {currentSetting.account_holder && (
                                                        <div>
                                                            <span className="text-xs text-slate-400">Bénéficiaire / Titulaire</span>
                                                            <div className="font-bold text-slate-900 dark:text-white">
                                                                {currentSetting.account_holder}
                                                            </div>
                                                        </div>
                                                    )}
                                                </div>

                                                {/* RIB / Account number with copy button */}
                                                {currentSetting.rib && (
                                                    <div className="p-3.5 rounded-xl bg-white dark:bg-[#141A2E] border border-slate-200 dark:border-slate-700/80 flex items-center justify-between gap-3">
                                                        <div className="min-w-0">
                                                            <span className="text-[10px] text-slate-400 uppercase font-semibold">
                                                                RIB / Numéro de compte / Identifiant
                                                            </span>
                                                            <div className="font-mono font-bold text-slate-900 dark:text-white text-sm sm:text-base tracking-wider truncate">
                                                                {currentSetting.rib}
                                                            </div>
                                                        </div>
                                                        <button
                                                            type="button"
                                                            onClick={() => handleCopy(currentSetting.rib!, currentSetting.key)}
                                                            className="px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-[#6C5CE7] hover:text-white text-xs font-semibold flex items-center gap-1.5 transition-colors shrink-0"
                                                        >
                                                            {copiedField === currentSetting.key ? (
                                                                <>
                                                                    <Check className="w-3.5 h-3.5 text-emerald-500" /> Copié
                                                                </>
                                                            ) : (
                                                                <>
                                                                    <Copy className="w-3.5 h-3.5" /> Copier
                                                                </>
                                                            )}
                                                        </button>
                                                    </div>
                                                )}

                                                {/* Transfer Motif Instruction */}
                                                <div className="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/40 text-xs text-amber-800 dark:text-amber-200 flex items-start gap-2.5">
                                                    <AlertCircle className="w-4 h-4 shrink-0 mt-0.5 text-amber-600" />
                                                    <div>
                                                        <strong>Motif obligatoire :</strong> Veuillez mentionner votre référence{' '}
                                                        <code className="px-1.5 py-0.5 rounded bg-amber-100 dark:bg-amber-900/60 font-mono font-bold">
                                                            {userReferenceHint}
                                                        </code>{' '}
                                                        lors du transfert.
                                                    </div>
                                                </div>
                                            </div>
                                        );
                                    })()}
                                </>
                            ) : (
                                <div className="p-4 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs text-slate-500">
                                    Les modalités de paiement sont en cours de mise à jour par l'administrateur.
                                </div>
                            )}
                        </Card>

                        {/* STEP 3: Proof Upload & Verification Form */}
                        <form onSubmit={handleSubmit}>
                            <Card className="p-6 sm:p-8 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
                                <div className="flex items-center gap-3 border-b border-slate-100 dark:border-slate-800/80 pb-4">
                                    <div className="w-9 h-9 rounded-xl bg-[#6C5CE7]/10 text-[#6C5CE7] flex items-center justify-center font-bold text-sm">
                                        3
                                    </div>
                                    <div>
                                        <h2 className="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                            <UploadCloud className="w-4 h-4 text-[#6C5CE7]" />
                                            Transmission du Reçu & Validation
                                        </h2>
                                        <p className="text-xs text-slate-500">
                                            Téléchargez votre preuve de virement (JPG, PNG ou PDF) pour activer votre pack.
                                        </p>
                                    </div>
                                </div>

                                <div className="space-y-4">
                                    {/* Reference Number */}
                                    <div>
                                        <label className="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                            Numéro d'opération / Référence transactionnelle *
                                        </label>
                                        <Input
                                            type="text"
                                            value={data.reference_number}
                                            onChange={(e) => setData('reference_number', e.target.value)}
                                            placeholder="Ex: VIR-8392019 ou N° bordereau agence"
                                            required
                                            className="w-full"
                                        />
                                        {errors.reference_number && (
                                            <p className="text-xs text-rose-500 font-semibold mt-1">
                                                {errors.reference_number}
                                            </p>
                                        )}
                                    </div>

                                    {/* Proof File Upload Box */}
                                    <div>
                                        <label className="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                            Reçu ou bordereau de paiement (JPG, PNG, WebP, PDF - Max 10 Mo) *
                                        </label>
                                        <div className="relative border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl p-6 text-center hover:border-[#6C5CE7] transition-colors bg-slate-50/50 dark:bg-slate-900/40">
                                            <input
                                                type="file"
                                                accept="image/jpeg,image/png,image/webp,application/pdf"
                                                onChange={handleFileChange}
                                                required
                                                className="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                            />
                                            <div className="flex flex-col items-center justify-center gap-2">
                                                <div className="w-12 h-12 rounded-2xl bg-[#6C5CE7]/10 text-[#6C5CE7] flex items-center justify-center">
                                                    {data.proof_file ? (
                                                        <FileCheck className="w-6 h-6 text-emerald-500" />
                                                    ) : (
                                                        <UploadCloud className="w-6 h-6" />
                                                    )}
                                                </div>
                                                {data.proof_file ? (
                                                    <div>
                                                        <p className="text-sm font-bold text-slate-900 dark:text-white">
                                                            {data.proof_file.name}
                                                        </p>
                                                        <p className="text-xs text-emerald-600 font-semibold mt-0.5">
                                                            Fichier prêt ({(data.proof_file.size / 1024 / 1024).toFixed(2)} Mo)
                                                        </p>
                                                    </div>
                                                ) : (
                                                    <div>
                                                        <p className="text-sm font-bold text-slate-700 dark:text-slate-300">
                                                            Cliquez ou glissez-déposez votre reçu ici
                                                        </p>
                                                        <p className="text-xs text-slate-400 mt-1">
                                                            Capture écran de l'application bancaire ou photo du reçu guichet
                                                        </p>
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                        {errors.proof_file && (
                                            <p className="text-xs text-rose-500 font-semibold mt-1">
                                                {errors.proof_file}
                                            </p>
                                        )}
                                    </div>

                                    {/* Customer Notes */}
                                    <div>
                                        <label className="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                                            Instructions ou adresse de livraison (Optionnel)
                                        </label>
                                        <textarea
                                            value={data.customer_notes}
                                            onChange={(e) => setData('customer_notes', e.target.value)}
                                            placeholder="Adresse de livraison au Maroc, nom de l'entreprise ou remarque..."
                                            rows={2}
                                            className="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-[#141A2E] text-slate-900 dark:text-white text-sm p-3 focus:ring-2 focus:ring-[#6C5CE7]"
                                        />
                                    </div>
                                </div>

                                <Button
                                    type="submit"
                                    variant="primary"
                                    size="lg"
                                    disabled={processing}
                                    className="w-full py-4 text-base font-bold shadow-lg shadow-[#6C5CE7]/30"
                                >
                                    {processing ? 'Validation en cours...' : 'Confirmer et Soumettre ma Commande'}
                                </Button>
                            </Card>
                        </form>
                    </div>

                    {/* RIGHT COLUMN: Realistic Live Card Mockup & Order Summary */}
                    <div className="lg:col-span-5 space-y-6 lg:sticky lg:top-8">
                        {/* Live Physical Card Mockup Preview */}
                        {pkg.physical_cards_included > 0 && currentDesign && (
                            <Card className="p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden relative">
                                <div className="flex items-center justify-between mb-4">
                                    <span className="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                                        <Layers className="w-3.5 h-3.5 text-[#6C5CE7]" /> Aperçu Gravure Laser
                                    </span>
                                    <Badge variant="primary">
                                        Carte #{previewCardIndex + 1}
                                    </Badge>
                                </div>

                                {/* Virtual NFC Card */}
                                <div
                                    className={`aspect-[1.586/1] w-full rounded-2xl p-6 flex flex-col justify-between border shadow-2xl relative overflow-hidden transition-all duration-300 ${getCardStyle(
                                        currentDesign.key
                                    )}`}
                                >
                                    {/* Specular Sheen Effect */}
                                    <div className="absolute inset-0 bg-gradient-to-tr from-white/0 via-white/10 to-transparent pointer-events-none" />

                                    {/* Card Header: Brand & Contactless Chip */}
                                    <div className="flex items-center justify-between z-10">
                                        <div className="flex items-center gap-2">
                                            <div className="w-7 h-7 rounded-lg bg-white/20 backdrop-blur-md flex items-center justify-center font-black text-xs">
                                                A
                                            </div>
                                            <span className="font-extrabold tracking-widest text-xs uppercase opacity-90">
                                                AURA CARD
                                            </span>
                                        </div>
                                        {/* Contactless Waves Icon */}
                                        <div className="w-6 h-6 rounded-full bg-white/10 flex items-center justify-center">
                                            <CreditCard className="w-3.5 h-3.5 opacity-80" />
                                        </div>
                                    </div>

                                    {/* Card Center: Material & Finish Label */}
                                    <div className="z-10 text-center py-2">
                                        <div className="font-extrabold text-base tracking-wide drop-shadow-sm">
                                            {getLocalized(currentDesign.name)}
                                        </div>
                                        <div className="text-[11px] opacity-75 uppercase tracking-widest font-mono mt-0.5">
                                            {currentDesign.material.replace('_', ' ')} • NFC Encrypted
                                        </div>
                                    </div>

                                    {/* Card Footer: Serial Preview */}
                                    <div className="flex items-center justify-between z-10 text-[10px] font-mono opacity-75">
                                        <span>SECURE HARDWARE TOKEN</span>
                                        <span>AURA-2026-NFC</span>
                                    </div>
                                </div>

                                <p className="text-[11px] text-slate-400 text-center mt-3">
                                    Finition sélectionnée : <strong className="text-slate-700 dark:text-slate-200">{getLocalized(currentDesign.name)}</strong>
                                </p>
                            </Card>
                        )}

                        {/* Order Summary Card */}
                        <Card className="p-6 sm:p-7 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
                            <h3 className="text-base font-bold text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800/80 pb-3">
                                Récapitulatif de la Commande
                            </h3>

                            <div className="space-y-4">
                                <div className="flex items-start justify-between">
                                    <div>
                                        <div className="font-bold text-slate-900 dark:text-white">
                                            {getLocalized(pkg.name)}
                                        </div>
                                        <div className="text-xs text-slate-500 mt-0.5">
                                            Abonnement Pro {pkg.duration_months} Mois inclus
                                        </div>
                                    </div>
                                    <div className="text-right">
                                        <div className="font-black text-lg text-slate-900 dark:text-white">
                                            {pkg.price.toFixed(2)} {pkg.currency}
                                        </div>
                                        <span className="text-[10px] text-emerald-600 font-semibold">TTC</span>
                                    </div>
                                </div>

                                <ul className="space-y-2 text-xs text-slate-600 dark:text-slate-300 border-t border-slate-100 dark:border-slate-800/80 pt-4">
                                    <li className="flex items-center justify-between">
                                        <span>Cartes NFC physiques incluses</span>
                                        <strong className="text-slate-900 dark:text-white">{pkg.physical_cards_included}</strong>
                                    </li>
                                    <li className="flex items-center justify-between">
                                        <span>Profils digitaux indépendants</span>
                                        <strong className="text-slate-900 dark:text-white">{pkg.profiles_included}</strong>
                                    </li>
                                    <li className="flex items-center justify-between">
                                        <span>Crédits de modification par profil</span>
                                        <strong className="text-slate-900 dark:text-white">{pkg.edit_credits_per_profile}</strong>
                                    </li>
                                    <li className="flex items-center justify-between">
                                        <span>Première publication</span>
                                        <strong className="text-emerald-600 font-bold">100% Gratuite (0 crédit)</strong>
                                    </li>
                                    <li className="flex items-center justify-between">
                                        <span>Livraison express au Maroc</span>
                                        <strong className="text-emerald-600 font-bold">Incluse</strong>
                                    </li>
                                </ul>

                                <div className="border-t-2 border-slate-200 dark:border-slate-800 pt-4 flex items-baseline justify-between">
                                    <span className="text-sm font-bold text-slate-900 dark:text-white">Total à régler :</span>
                                    <div className="text-right">
                                        <span className="text-2xl font-black text-[#6C5CE7]">
                                            {pkg.price.toFixed(2)} {pkg.currency}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div className="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800/80 space-y-2">
                                <div className="flex items-center gap-2 text-xs font-bold text-slate-700 dark:text-slate-300">
                                    <ShieldCheck className="w-4 h-4 text-emerald-500" />
                                    Garantie & Support AURA CARD
                                </div>
                                <p className="text-[11px] text-slate-500 dark:text-slate-400">
                                    Puce NFC NTAG cryptée garantie à vie. Reçu avec valeur juridique pour comptabilité d'entreprise au Maroc.
                                </p>
                            </div>
                        </Card>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
