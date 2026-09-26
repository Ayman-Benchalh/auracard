import React, { useState } from 'react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { AppLayout } from '@/Layouts/AppLayout';
import { Card } from '@/Components/UI/Card';
import { Button } from '@/Components/UI/Button';
import { Input } from '@/Components/UI/Input';
import { Badge } from '@/Components/UI/Badge';
import {
    Receipt,
    CreditCard,
    CheckCircle2,
    Clock,
    XCircle,
    Truck,
    ArrowLeft,
    Sparkles,
    Smartphone,
    Building2,
    Banknote,
    UploadCloud,
    FileCheck,
    Download,
    Layers,
    ShieldCheck,
    AlertCircle,
    Check,
    Copy
} from 'lucide-react';
import { PageProps } from '@/types';

interface AvailableDesign {
    key: string;
    name: Record<string, string> | string;
    color: string;
    material: string;
    preview_image?: string;
}

interface PaymentRequestItem {
    id: string;
    status: string;
    status_label: string;
    payment_method: string;
    reference_number: string;
    rejection_reason?: string | null;
    created_at: string;
    processed_at?: string | null;
}

interface CardAllocationItem {
    id: string;
    card_serial?: string;
    card_status?: string;
    design_name?: Record<string, string> | string;
    design_color?: string;
    credits_allocated?: number;
}

interface OrderDetailProps {
    order: {
        id: string;
        order_number: string;
        status: string;
        total_amount: number;
        currency: string;
        customer_notes?: string | null;
        card_selections?: string[] | null;
        created_at: string;
        updated_at: string;
        package?: {
            id: string;
            key: string;
            name: Record<string, string> | string;
            description: Record<string, string> | string;
            duration_months: number;
            physical_cards_included: number;
            profiles_included: number;
            edit_credits_per_profile: number;
        } | null;
        payment_requests: PaymentRequestItem[];
        purchase?: {
            id: string;
            status: string;
            starts_at?: string;
            ends_at?: string;
            card_allocations: CardAllocationItem[];
        } | null;
    };
    availableDesigns: Record<string, AvailableDesign>;
    paymentSettings: any[];
    userReferenceHint: string;
}

export default function OrderDetail({
    order,
    availableDesigns,
    paymentSettings,
    userReferenceHint,
}: OrderDetailProps) {
    const { locale = 'fr' } = usePage<PageProps>().props;
    const [copiedRib, setCopiedRib] = useState(false);

    const getLocalized = (field: any, defaultStr: string = '') => {
        if (!field) return defaultStr;
        if (typeof field === 'string') return field;
        return field[locale] || field['fr'] || field['en'] || defaultStr;
    };

    const latestPayment = order.payment_requests[0] || null;

    // Form for uploading proof if order is still pending
    const { data, setData, post, processing, errors } = useForm<{
        payment_method: string;
        reference_number: string;
        customer_notes: string;
        proof_file: File | null;
    }>({
        payment_method: 'bank_transfer',
        reference_number: '',
        customer_notes: '',
        proof_file: null,
    });

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        if (e.target.files && e.target.files[0]) {
            setData('proof_file', e.target.files[0]);
        }
    };

    const handleProofSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(`/orders/${order.id}/proof`);
    };

    const activeBankSetting = paymentSettings.find(
        (s) => s.key === 'manual_bank_transfer'
    ) || paymentSettings[0];

    const copyRib = (rib: string) => {
        navigator.clipboard.writeText(rib);
        setCopiedRib(true);
        setTimeout(() => setCopiedRib(false), 2000);
    };

    // Calculate Step Index
    const steps = [
        { key: 'placed', label: 'Commande Initiée', completed: true },
        {
            key: 'review',
            label: 'Examen du Reçu',
            completed: ['under_review', 'approved', 'fulfilled'].includes(order.status),
            current: order.status === 'under_review',
        },
        {
            key: 'approved',
            label: 'Validée & Gravure NFC',
            completed: ['approved', 'fulfilled'].includes(order.status),
            current: order.status === 'approved',
        },
        {
            key: 'fulfilled',
            label: 'Livrée & Activée',
            completed: order.status === 'fulfilled',
            current: order.status === 'fulfilled',
        },
    ];

    return (
        <AppLayout title={`Commande ${order.order_number}`}>
            <Head title={`Commande ${order.order_number}`} />

            <div className="max-w-5xl mx-auto space-y-8 py-8 px-4 sm:px-6">
                {/* Back button */}
                <div className="flex items-center justify-between">
                    <Link
                        href="/orders"
                        className="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-slate-900 dark:hover:text-white transition-colors"
                    >
                        <ArrowLeft className="w-4 h-4" />
                        Toutes mes commandes
                    </Link>

                    <div className="font-mono text-xs font-semibold text-slate-400">
                        {order.order_number}
                    </div>
                </div>

                {/* Status Tracker Bar */}
                <Card className="p-6 sm:p-8 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-6">
                    <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800/80 pb-6">
                        <div>
                            <span className="text-xs font-bold uppercase tracking-wider text-slate-400">
                                Statut actuel de la commande
                            </span>
                            <div className="flex items-center gap-3 mt-1">
                                <h1 className="text-2xl font-black text-slate-900 dark:text-white">
                                    {order.status === 'approved' && 'Commande Validée & En Production'}
                                    {order.status === 'under_review' && 'Reçu en Cours d\'Examen'}
                                    {order.status === 'pending' && 'En Attente de Paiement'}
                                    {order.status === 'fulfilled' && 'Cartes Livrées & Profils Actifs'}
                                    {order.status === 'rejected' && 'Paiement Non Validé'}
                                </h1>
                            </div>
                        </div>

                        <div className="text-left sm:text-right">
                            <span className="text-xs text-slate-400 block font-medium">Montant total</span>
                            <span className="text-2xl font-black text-[#6C5CE7]">
                                {order.total_amount.toFixed(2)} {order.currency}
                            </span>
                        </div>
                    </div>

                    {/* Stepper */}
                    <div className="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        {steps.map((st, i) => (
                            <div
                                key={st.key}
                                className={`p-4 rounded-2xl border text-center transition-all ${
                                    st.current
                                        ? 'border-[#6C5CE7] bg-[#6C5CE7]/10 font-bold'
                                        : st.completed
                                        ? 'border-emerald-500/30 bg-emerald-500/5 text-emerald-700 dark:text-emerald-300'
                                        : 'border-slate-200 dark:border-slate-800 opacity-50'
                                }`}
                            >
                                <div className="text-xs font-bold mb-1 flex items-center justify-center gap-1.5">
                                    {st.completed ? (
                                        <CheckCircle2 className="w-4 h-4 text-emerald-500" />
                                    ) : (
                                        <Clock className="w-4 h-4 text-slate-400" />
                                    )}
                                    <span>Étape {i + 1}</span>
                                </div>
                                <div className="text-xs truncate">{st.label}</div>
                            </div>
                        ))}
                    </div>
                </Card>

                <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    {/* LEFT COLUMN: Hardware Choices & Active Cards */}
                    <div className="lg:col-span-7 space-y-6">
                        {/* Chosen Card Finishes */}
                        {order.card_selections && order.card_selections.length > 0 && (
                            <Card className="p-6 sm:p-7 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                                <h3 className="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                                    <Layers className="w-4 h-4 text-[#6C5CE7]" />
                                    Finitions des Cartes NFC Gravées ({order.card_selections.length})
                                </h3>

                                <div className="space-y-3">
                                    {order.card_selections.map((selItem: any, idx) => {
                                        const selKey = typeof selItem === 'string' ? selItem : selItem?.key;
                                        const design = availableDesigns[selKey] || selItem;
                                        return (
                                            <div
                                                key={idx}
                                                className="p-4 rounded-2xl border border-slate-200 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900/40 flex items-center justify-between"
                                            >
                                                <div className="flex items-center gap-3">
                                                    <div
                                                        className="w-10 h-10 rounded-xl border border-black/10 flex items-center justify-center text-white shadow-inner font-bold text-xs"
                                                        style={{
                                                            backgroundColor: design?.color || '#111827',
                                                        }}
                                                    >
                                                        #{idx + 1}
                                                    </div>
                                                    <div>
                                                        <div className="text-sm font-bold text-slate-900 dark:text-white">
                                                            {design ? getLocalized(design.name) : selKey}
                                                        </div>
                                                        <span className="text-[11px] text-slate-400 uppercase font-semibold">
                                                            {design?.material?.replace('_', ' ') || 'PVC Polymère'}
                                                        </span>
                                                    </div>
                                                </div>

                                                <Badge variant="primary">Prévue pour gravure</Badge>
                                            </div>
                                        );
                                    })}
                                </div>
                            </Card>
                        )}

                        {/* Allocated Hardware (When Approved) */}
                        {order.purchase && order.purchase.card_allocations && order.purchase.card_allocations.length > 0 && (
                            <Card className="p-6 sm:p-7 rounded-3xl border border-emerald-500/30 bg-emerald-500/5 shadow-sm space-y-4">
                                <h3 className="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 border-b border-emerald-500/20 pb-3">
                                    <Smartphone className="w-4 h-4 text-emerald-600" />
                                    Cartes NFC Allouées & Encodées
                                </h3>
                                <div className="space-y-3">
                                    {order.purchase.card_allocations.map((alloc) => (
                                        <div
                                            key={alloc.id}
                                            className="p-4 rounded-2xl bg-white dark:bg-[#141A2E] border border-slate-200 dark:border-slate-800 flex items-center justify-between"
                                        >
                                            <div className="space-y-1">
                                                <div className="text-xs font-mono font-bold text-slate-900 dark:text-white">
                                                    N° Série : {alloc.card_serial || 'Attribution en cours'}
                                                </div>
                                                <div className="text-xs text-slate-500">
                                                    Finition : {getLocalized(alloc.design_name, 'Standard')} • {alloc.credits_allocated || 3} Crédits inclus
                                                </div>
                                            </div>
                                            <Badge variant="success">
                                                {alloc.card_status || 'Active'}
                                            </Badge>
                                        </div>
                                    ))}
                                </div>
                            </Card>
                        )}

                        {/* Customer Notes */}
                        {order.customer_notes && (
                            <Card className="p-6 rounded-3xl border border-slate-200 dark:border-slate-800 space-y-2">
                                <h4 className="text-xs font-bold uppercase tracking-wider text-slate-400">
                                    Remarques & Adresse transmises :
                                </h4>
                                <p className="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-line">
                                    {order.customer_notes}
                                </p>
                            </Card>
                        )}
                    </div>

                    {/* RIGHT COLUMN: Payment Verification & Proof Management */}
                    <div className="lg:col-span-5 space-y-6">
                        {/* Latest Payment Status Box */}
                        {latestPayment ? (
                            <Card className="p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                                <h3 className="text-sm font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                                    <Receipt className="w-4 h-4 text-[#6C5CE7]" /> Preuve de Paiement Transmise
                                </h3>

                                <div className="space-y-3 text-xs">
                                    <div className="flex items-center justify-between">
                                        <span className="text-slate-500">État du règlement :</span>
                                        <Badge
                                            variant={
                                                latestPayment.status === 'approved'
                                                    ? 'success'
                                                    : latestPayment.status === 'under_review'
                                                    ? 'primary'
                                                    : 'warning'
                                            }
                                        >
                                            {latestPayment.status_label || latestPayment.status}
                                        </Badge>
                                    </div>

                                    <div className="flex items-center justify-between">
                                        <span className="text-slate-500">Mode choisi :</span>
                                        <span className="font-bold text-slate-900 dark:text-white capitalize">
                                            {latestPayment.payment_method.replace('_', ' ')}
                                        </span>
                                    </div>

                                    <div className="flex items-center justify-between">
                                        <span className="text-slate-500">Référence transaction :</span>
                                        <span className="font-mono font-bold text-slate-900 dark:text-white">
                                            {latestPayment.reference_number}
                                        </span>
                                    </div>

                                    <div className="flex items-center justify-between">
                                        <span className="text-slate-500">Date de transmission :</span>
                                        <span className="text-slate-700 dark:text-slate-300">
                                            {new Date(latestPayment.created_at).toLocaleDateString(locale, {
                                                day: '2-digit',
                                                month: 'short',
                                                year: 'numeric',
                                                hour: '2-digit',
                                                minute: '2-digit',
                                            })}
                                        </span>
                                    </div>

                                    {latestPayment.rejection_reason && (
                                        <div className="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-800/40 text-rose-800 dark:text-rose-200 mt-2">
                                            <strong>Motif du rejet :</strong> {latestPayment.rejection_reason}
                                        </div>
                                    )}

                                    <div className="pt-2">
                                        <a
                                            href={`/orders/${order.id}/proofs/${latestPayment.id}/download`}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            <Button variant="outline" size="sm" className="w-full flex items-center justify-center gap-2">
                                                <Download className="w-3.5 h-3.5" />
                                                Télécharger le reçu sécurisé
                                            </Button>
                                        </a>
                                    </div>
                                </div>
                            </Card>
                        ) : (
                            /* Pending Order: Upload Proof Box */
                            <Card className="p-6 rounded-3xl border-2 border-amber-300 dark:border-amber-700/60 shadow-sm space-y-4">
                                <div className="flex items-center gap-2 text-amber-700 dark:text-amber-400 font-bold text-sm">
                                    <AlertCircle className="w-4 h-4 shrink-0" />
                                    Preuve de paiement requise
                                </div>
                                <p className="text-xs text-slate-500">
                                    Veuillez effectuer le virement avec notre RIB ci-dessous et transmettre votre reçu pour validation.
                                </p>

                                {activeBankSetting?.rib && (
                                    <div className="p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-1">
                                        <span className="text-[10px] text-slate-400 uppercase font-semibold">
                                            RIB Attijariwafa Bank
                                        </span>
                                        <div className="font-mono text-xs font-bold text-slate-900 dark:text-white flex items-center justify-between">
                                            <span>{activeBankSetting.rib}</span>
                                            <button
                                                type="button"
                                                onClick={() => copyRib(activeBankSetting.rib)}
                                                className="text-[10px] text-[#6C5CE7] hover:underline"
                                            >
                                                {copiedRib ? 'Copié' : 'Copier'}
                                            </button>
                                        </div>
                                    </div>
                                )}

                                <form onSubmit={handleProofSubmit} className="space-y-3 pt-2">
                                    <div>
                                        <label className="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                            N° Référence du virement *
                                        </label>
                                        <Input
                                            type="text"
                                            value={data.reference_number}
                                            onChange={(e) => setData('reference_number', e.target.value)}
                                            placeholder="Ex: VIR-849201"
                                            required
                                            className="w-full text-xs"
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                                            Reçu de paiement (JPG, PNG, PDF) *
                                        </label>
                                        <input
                                            type="file"
                                            accept="image/jpeg,image/png,image/webp,application/pdf"
                                            onChange={handleFileChange}
                                            required
                                            className="text-xs w-full file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#6C5CE7]/10 file:text-[#6C5CE7]"
                                        />
                                    </div>

                                    <Button
                                        type="submit"
                                        variant="primary"
                                        size="sm"
                                        disabled={processing}
                                        className="w-full"
                                    >
                                        {processing ? 'Envoi...' : 'Transmettre la preuve'}
                                    </Button>
                                </form>
                            </Card>
                        )}

                        {/* Package Details Recap */}
                        {order.package && (
                            <Card className="p-6 rounded-3xl border border-slate-200 dark:border-slate-800 space-y-3 text-xs text-slate-600 dark:text-slate-400">
                                <h4 className="font-bold text-slate-900 dark:text-white text-sm">
                                    Inclusions du {getLocalized(order.package.name)}
                                </h4>
                                <ul className="space-y-1.5">
                                    <li>• {order.package.physical_cards_included} Carte(s) NFC haute finition</li>
                                    <li>• {order.package.profiles_included} Profil(s) digital pro indépendant(s)</li>
                                    <li>• {order.package.edit_credits_per_profile} Crédits de modification par profil</li>
                                    <li>• Accès vCard 4.0 & QR Code vectoriel dynamique</li>
                                    <li>• Hébergement cloud & Analytics 12 Mois</li>
                                </ul>
                            </Card>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
