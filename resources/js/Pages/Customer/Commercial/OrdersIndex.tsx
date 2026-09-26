import React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import { AppLayout } from '@/Layouts/AppLayout';
import { Card } from '@/Components/UI/Card';
import { Button } from '@/Components/UI/Button';
import { Badge } from '@/Components/UI/Badge';
import {
    Receipt,
    CreditCard,
    CheckCircle2,
    Clock,
    XCircle,
    Truck,
    ArrowRight,
    Sparkles,
    Smartphone,
    ShoppingBag,
    Eye
} from 'lucide-react';
import { PageProps } from '@/types';

interface OrderItem {
    id: string;
    order_number: string;
    status: string;
    total_amount: number;
    currency: string;
    card_selections?: string[] | null;
    package?: {
        key: string;
        name: Record<string, string> | string;
        physical_cards_included: number;
        profiles_included: number;
    } | null;
    latest_payment?: {
        status: string;
        status_label: string;
        reference_number: string;
        created_at: string;
    } | null;
    created_at: string;
}

interface ActivePurchase {
    id: string;
    package_name: Record<string, string> | string;
    package_key: string;
    starts_at?: string;
    ends_at?: string;
    card_allocations_count: number;
    profile_allocations_count: number;
}

interface Props {
    orders: {
        data: OrderItem[];
        links: any[];
        current_page: number;
        last_page: number;
    };
    activePurchases: ActivePurchase[];
}

export default function OrdersIndex({ orders, activePurchases }: Props) {
    const { locale = 'fr' } = usePage<PageProps>().props;

    const getLocalized = (field: any, defaultStr: string = '') => {
        if (!field) return defaultStr;
        if (typeof field === 'string') return field;
        return field[locale] || field['fr'] || field['en'] || defaultStr;
    };

    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'approved':
                return (
                    <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                        <CheckCircle2 className="w-3.5 h-3.5" /> Validée & Active
                    </span>
                );
            case 'under_review':
                return (
                    <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                        <Clock className="w-3.5 h-3.5" /> En cours d'examen
                    </span>
                );
            case 'fulfilled':
                return (
                    <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20">
                        <Truck className="w-3.5 h-3.5" /> Livrée & Configurée
                    </span>
                );
            case 'rejected':
                return (
                    <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                        <XCircle className="w-3.5 h-3.5" /> Rejetée
                    </span>
                );
            case 'pending':
            default:
                return (
                    <span className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                        <Clock className="w-3.5 h-3.5" /> En attente de paiement
                    </span>
                );
        }
    };

    return (
        <AppLayout title="Mes Commandes & Abonnements">
            <Head title="Mes Commandes" />

            <div className="max-w-6xl mx-auto space-y-10 py-8 px-4 sm:px-6">
                {/* Header */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 className="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-3">
                            <Receipt className="w-7 h-7 text-[#6C5CE7]" />
                            Mes Commandes & Forfaits
                        </h1>
                        <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
                            Suivez l'état de fabrication de vos cartes physiques et l'activation de vos profils.
                        </p>
                    </div>

                    <Link href="/pricing">
                        <Button variant="primary" className="shadow-md shadow-[#6C5CE7]/20 flex items-center gap-2">
                            <Sparkles className="w-4 h-4" />
                            Commander un nouveau pack
                        </Button>
                    </Link>
                </div>

                {/* Active Subscriptions Banner */}
                {activePurchases && activePurchases.length > 0 && (
                    <div className="space-y-4">
                        <h2 className="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                            <Sparkles className="w-4 h-4 text-emerald-500" /> Vos Forfaits Commerciaux Actifs
                        </h2>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {activePurchases.map((purchase) => (
                                <Card
                                    key={purchase.id}
                                    className="p-5 rounded-2xl border border-emerald-500/20 bg-gradient-to-br from-emerald-500/5 via-transparent to-transparent flex items-center justify-between"
                                >
                                    <div className="space-y-1">
                                        <div className="flex items-center gap-2">
                                            <span className="font-extrabold text-base text-slate-900 dark:text-white">
                                                {getLocalized(purchase.package_name)}
                                            </span>
                                            <Badge variant="success">Actif</Badge>
                                        </div>
                                        <div className="text-xs text-slate-500 flex items-center gap-3">
                                            <span>{purchase.card_allocations_count} Cartes NFC</span>
                                            <span>•</span>
                                            <span>{purchase.profile_allocations_count} Profils inclus</span>
                                        </div>
                                    </div>

                                    <div className="text-right">
                                        <span className="text-[10px] text-slate-400 block uppercase font-semibold">
                                            Expire le
                                        </span>
                                        <span className="text-xs font-mono font-bold text-slate-700 dark:text-slate-300">
                                            {purchase.ends_at
                                                ? new Date(purchase.ends_at).toLocaleDateString(locale, {
                                                      year: 'numeric',
                                                      month: 'short',
                                                      day: 'numeric',
                                                  })
                                                : 'Illimité'}
                                        </span>
                                    </div>
                                </Card>
                            ))}
                        </div>
                    </div>
                )}

                {/* Orders List */}
                <div className="space-y-4">
                    <h2 className="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                        <ShoppingBag className="w-4 h-4 text-[#6C5CE7]" /> Historique des Commandes
                    </h2>

                    {orders.data && orders.data.length > 0 ? (
                        <div className="space-y-3">
                            {orders.data.map((order) => (
                                <Card
                                    key={order.id}
                                    className="p-5 sm:p-6 rounded-2xl border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                                >
                                    <div className="space-y-1.5 min-w-0 flex-1">
                                        <div className="flex flex-wrap items-center gap-2.5">
                                            <span className="font-mono font-black text-sm text-slate-900 dark:text-white">
                                                {order.order_number}
                                            </span>
                                            {getStatusBadge(order.status)}
                                        </div>

                                        <div className="flex flex-wrap items-center gap-4 text-xs text-slate-500 dark:text-slate-400">
                                            <span>
                                                Pack : <strong className="text-slate-800 dark:text-slate-200">{getLocalized(order.package?.name, 'Pack Standard')}</strong>
                                            </span>
                                            <span>•</span>
                                            <span>
                                                Date : {new Date(order.created_at).toLocaleDateString(locale, {
                                                    day: '2-digit',
                                                    month: 'short',
                                                    year: 'numeric',
                                                })}
                                            </span>
                                            {order.card_selections && order.card_selections.length > 0 && (
                                                <>
                                                    <span>•</span>
                                                    <span>{order.card_selections.length} Carte(s) gravée(s)</span>
                                                </>
                                            )}
                                        </div>
                                    </div>

                                    <div className="flex items-center justify-between sm:justify-end gap-6 shrink-0 border-t sm:border-t-0 pt-3 sm:pt-0 border-slate-100 dark:border-slate-800">
                                        <div className="text-left sm:text-right">
                                            <span className="text-lg font-black text-slate-900 dark:text-white">
                                                {order.total_amount.toFixed(2)} {order.currency}
                                            </span>
                                            <span className="text-[10px] text-slate-400 block font-semibold uppercase">
                                                TTC
                                            </span>
                                        </div>

                                        <Link href={`/orders/${order.id}`}>
                                            <Button variant="outline" size="sm" className="flex items-center gap-1.5">
                                                <Eye className="w-3.5 h-3.5" />
                                                <span>Détails</span>
                                            </Button>
                                        </Link>
                                    </div>
                                </Card>
                            ))}
                        </div>
                    ) : (
                        <Card className="p-12 text-center rounded-3xl border border-dashed border-slate-300 dark:border-slate-800 space-y-4">
                            <div className="w-14 h-14 rounded-2xl bg-[#6C5CE7]/10 text-[#6C5CE7] flex items-center justify-center mx-auto">
                                <ShoppingBag className="w-7 h-7" />
                            </div>
                            <div className="space-y-1">
                                <h3 className="text-base font-bold text-slate-900 dark:text-white">
                                    Aucune commande enregistrée
                                </h3>
                                <p className="text-xs text-slate-500 max-w-sm mx-auto">
                                    Vous n'avez pas encore commandé de carte NFC physique ou de pack digital.
                                </p>
                            </div>
                            <Link href="/pricing">
                                <Button variant="primary" size="sm" className="mt-2">
                                    Découvrir nos offres
                                </Button>
                            </Link>
                        </Card>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
