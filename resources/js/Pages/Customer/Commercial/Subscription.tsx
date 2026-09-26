import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { AppLayout } from '@/Layouts/AppLayout';
import { Card } from '@/Components/UI/Card';
import { Button } from '@/Components/UI/Button';
import { Badge } from '@/Components/UI/Badge';
import {
    Sparkles,
    Calendar,
    CreditCard,
    ArrowUpRight,
    Clock,
    CheckCircle2,
    XCircle,
    FileText,
    Download,
    AlertCircle,
    Infinity as InfinityIcon
} from 'lucide-react';

interface SubscriptionData {
    id: string;
    status: string;
    plan?: {
        key: string;
        name: string;
    } | null;
    price: number;
    currency: string;
    billing_period: string;
    plan_snapshot?: any;
    starts_at?: string;
    ends_at?: string;
    has_unlimited_edits: boolean;
}

interface PaymentRequestItem {
    id: string;
    plan_name?: string;
    amount: number;
    currency: string;
    status: 'pending' | 'approved' | 'rejected';
    status_label: string;
    reference_number?: string;
    rejection_reason?: string;
    created_at: string;
    processed_at?: string;
}

interface Props {
    subscription?: SubscriptionData | null;
    editCreditBalance: number;
    paymentRequests: PaymentRequestItem[];
}

export default function Subscription({ subscription, editCreditBalance, paymentRequests }: Props) {
    const formatDate = (dateStr?: string) => {
        if (!dateStr) return '-';
        return new Date(dateStr).toLocaleDateString('fr-FR', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    };

    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'approved':
            case 'active':
                return <Badge variant="success">Actif / Validé</Badge>;
            case 'pending':
                return <Badge variant="warning">En attente de vérification</Badge>;
            case 'rejected':
                return <Badge variant="danger">Refusé</Badge>;
            case 'expired':
                return <Badge variant="neutral">Expiré</Badge>;
            default:
                return <Badge variant="neutral">{status}</Badge>;
        }
    };

    return (
        <AppLayout title="Mon Abonnement & Facturation">
            <Head title="Mon Abonnement & Facturation" />

            <div className="max-w-5xl mx-auto space-y-8 py-6 px-4">
                {/* Header */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 className="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                            Abonnement & Facturation
                        </h1>
                        <p className="text-sm text-slate-500 dark:text-slate-400">
                            Gérez votre formule, vos crédits de modification et l'historique de vos paiements.
                        </p>
                    </div>

                    <Link href="/pricing">
                        <Button variant="primary" className="flex items-center gap-2">
                            <Sparkles className="w-4 h-4" />
                            <span>Explorer les forfaits</span>
                        </Button>
                    </Link>
                </div>

                {/* Subscription Status Card & Credit Balance */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {/* Active Plan Card */}
                    <div className="md:col-span-2">
                        <Card className="p-6 relative overflow-hidden bg-gradient-to-br from-white via-white to-slate-50 dark:from-[#141A2E] dark:via-[#141A2E] dark:to-[#101524] border border-slate-200 dark:border-slate-800">
                            <div className="flex items-start justify-between">
                                <div>
                                    <span className="text-xs font-bold uppercase tracking-wider text-slate-400">
                                        Formule active
                                    </span>
                                    <h2 className="text-2xl font-black text-slate-900 dark:text-white mt-1">
                                        {subscription?.plan?.name || 'Formule Gratuite (Découverte)'}
                                    </h2>
                                </div>
                                <div>
                                    {subscription ? getStatusBadge(subscription.status) : <Badge variant="neutral">Standard</Badge>}
                                </div>
                            </div>

                            {subscription ? (
                                <div className="mt-6 grid grid-cols-2 sm:grid-cols-3 gap-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                                    <div>
                                        <span className="text-xs text-slate-400 flex items-center gap-1.5">
                                            <CreditCard className="w-3.5 h-3.5" /> Tarif
                                        </span>
                                        <p className="font-bold text-slate-900 dark:text-white mt-1">
                                            {subscription.price} {subscription.currency} / an
                                        </p>
                                    </div>
                                    <div>
                                        <span className="text-xs text-slate-400 flex items-center gap-1.5">
                                            <Calendar className="w-3.5 h-3.5" /> Date d'effet
                                        </span>
                                        <p className="font-semibold text-slate-700 dark:text-slate-300 mt-1">
                                            {formatDate(subscription.starts_at)}
                                        </p>
                                    </div>
                                    <div>
                                        <span className="text-xs text-slate-400 flex items-center gap-1.5">
                                            <Clock className="w-3.5 h-3.5" /> Échéance
                                        </span>
                                        <p className="font-semibold text-slate-700 dark:text-slate-300 mt-1">
                                            {formatDate(subscription.ends_at)}
                                        </p>
                                    </div>
                                </div>
                            ) : (
                                <div className="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800 text-sm text-slate-500">
                                    <p>Vous utilisez actuellement l'accès de base. Passez à une formule Pro ou Business pour débloquer les publications illimitées et les fonctionnalités avancées.</p>
                                    <div className="mt-4">
                                        <Link href="/pricing">
                                            <Button variant="outline" size="sm">Découvrir les offres Pro</Button>
                                        </Link>
                                    </div>
                                </div>
                            )}

                            {subscription?.has_unlimited_edits && (
                                <div className="mt-4 p-3 bg-emerald-50 dark:bg-emerald-950/30 rounded-xl border border-emerald-200 dark:border-emerald-800/50 flex items-center gap-2 text-xs text-emerald-800 dark:text-emerald-300">
                                    <InfinityIcon className="w-4 h-4 shrink-0 text-emerald-600" />
                                    <span><strong>Avantage Pro activé :</strong> Vous bénéficiez des modifications et republications illimitées sans coût de crédit.</span>
                                </div>
                            )}
                        </Card>
                    </div>

                    {/* Credit Balance Card */}
                    <div>
                        <Card className="p-6 h-full flex flex-col justify-between bg-gradient-to-br from-[#6C5CE7]/5 via-white to-white dark:from-[#6C5CE7]/10 dark:via-[#141A2E] dark:to-[#141A2E] border border-slate-200 dark:border-slate-800">
                            <div>
                                <span className="text-xs font-bold uppercase tracking-wider text-[#6C5CE7]">
                                    Crédits de modification
                                </span>
                                <div className="mt-3 flex items-baseline gap-2">
                                    <span className="text-4xl font-black text-slate-900 dark:text-white">
                                        {editCreditBalance}
                                    </span>
                                    <span className="text-xs text-slate-500">crédit{editCreditBalance > 1 ? 's' : ''} disponible{editCreditBalance > 1 ? 's' : ''}</span>
                                </div>
                                <p className="text-xs text-slate-500 mt-2">
                                    1 crédit = 1 republication avec modification du profil. Les republications sans modification sont toujours gratuites (0 crédit).
                                </p>
                            </div>

                            <div className="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                                <Link href="/pricing" className="text-xs font-bold text-[#6C5CE7] hover:underline flex items-center gap-1">
                                    <span>Recharger ou passer en illimité</span>
                                    <ArrowUpRight className="w-3.5 h-3.5" />
                                </Link>
                            </div>
                        </Card>
                    </div>
                </div>

                {/* Payment Requests History Table */}
                <div className="space-y-4">
                    <h3 className="text-lg font-bold text-slate-900 dark:text-white">
                        Historique des demandes de règlement
                    </h3>

                    {paymentRequests.length === 0 ? (
                        <Card className="p-8 text-center space-y-2 text-slate-400">
                            <FileText className="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600" />
                            <p className="text-sm font-medium">Aucune demande de paiement pour le moment.</p>
                            <p className="text-xs text-slate-500">Vos reçus de virement et versements apparaîtront ici.</p>
                        </Card>
                    ) : (
                        <Card className="p-0 overflow-hidden border border-slate-200 dark:border-slate-800">
                            <div className="overflow-x-auto">
                                <table className="w-full text-left text-sm">
                                    <thead className="bg-slate-50 dark:bg-slate-900/60 text-xs uppercase tracking-wider text-slate-500 font-bold border-b border-slate-200 dark:border-slate-800">
                                        <tr>
                                            <th className="py-3.5 px-4">Date</th>
                                            <th className="py-3.5 px-4">Formule</th>
                                            <th className="py-3.5 px-4">Montant</th>
                                            <th className="py-3.5 px-4">Réf. Virement</th>
                                            <th className="py-3.5 px-4">Statut</th>
                                            <th className="py-3.5 px-4 text-right">Reçu</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-slate-100 dark:divide-slate-800">
                                        {paymentRequests.map((req) => (
                                            <tr key={req.id} className="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                                                <td className="py-3.5 px-4 text-slate-600 dark:text-slate-400 whitespace-nowrap">
                                                    {formatDate(req.created_at)}
                                                </td>
                                                <td className="py-3.5 px-4 font-semibold text-slate-900 dark:text-white whitespace-nowrap">
                                                    {req.plan_name || 'Forfait Annuel'}
                                                </td>
                                                <td className="py-3.5 px-4 font-bold text-slate-900 dark:text-white whitespace-nowrap">
                                                    {req.amount} {req.currency}
                                                </td>
                                                <td className="py-3.5 px-4 font-mono text-xs text-slate-500 whitespace-nowrap">
                                                    {req.reference_number || '-'}
                                                </td>
                                                <td className="py-3.5 px-4 whitespace-nowrap">
                                                    {getStatusBadge(req.status)}
                                                    {req.status === 'rejected' && req.rejection_reason && (
                                                        <p className="text-xs text-rose-500 mt-1 max-w-xs truncate" title={req.rejection_reason}>
                                                            {req.rejection_reason}
                                                        </p>
                                                    )}
                                                </td>
                                                <td className="py-3.5 px-4 text-right whitespace-nowrap">
                                                    <a
                                                        href={`/payment-requests/${req.id}/proof`}
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        className="inline-flex items-center gap-1.5 text-xs font-semibold text-[#6C5CE7] hover:underline"
                                                    >
                                                        <Download className="w-3.5 h-3.5" />
                                                        <span>Preuve</span>
                                                    </a>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </Card>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
