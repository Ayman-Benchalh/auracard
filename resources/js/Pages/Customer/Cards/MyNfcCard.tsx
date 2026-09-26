import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { AppLayout } from '@/Layouts/AppLayout';
import { Card } from '@/Components/UI/Card';
import { Badge } from '@/Components/UI/Badge';
import { Button } from '@/Components/UI/Button';
import { useTranslation } from '@/Hooks/useTranslation';
import {
    CreditCard,
    Smartphone,
    Plus,
    PauseCircle,
    PlayCircle,
    AlertOctagon,
    ShieldCheck,
    Sparkles,
} from 'lucide-react';

interface CardItem {
    id: string;
    serial_number: string;
    status: 'inventory' | 'reserved' | 'sold' | 'unclaimed' | 'claimed' | 'active' | 'suspended' | 'lost' | 'replaced' | 'disabled';
    status_label: string;
    status_color: string;
    card_type: string | null;
    claimed_at: string | null;
    activated_at: string | null;
    entitlement: {
        id: string;
        initial_credits_amount: number;
        is_initial_credits_granted: boolean;
        status: string;
        status_label: string;
    } | null;
}

interface MyNfcCardProps {
    cards: CardItem[];
}

export default function MyNfcCard({ cards }: MyNfcCardProps) {
    const { t } = useTranslation();
    const [confirmingLostId, setConfirmingLostId] = useState<string | null>(null);

    const handleToggleStatus = (cardId: string) => {
        router.patch(`/cards/${cardId}/toggle-status`, {}, {
            preserveScroll: true,
        });
    };

    const handleReportLost = (cardId: string) => {
        router.post(`/cards/${cardId}/lost`, {}, {
            preserveScroll: true,
            onFinish: () => setConfirmingLostId(null),
        });
    };

    const getCardSkinClass = (cardType: string | null) => {
        switch (cardType) {
            case 'matte_black':
                return 'bg-gradient-to-tr from-[#111319] via-[#1A1E29] to-[#0A0C10] text-white border-slate-700/60 shadow-2xl shadow-black/40';
            case 'metal_gold':
                return 'bg-gradient-to-tr from-[#785918] via-[#B88E34] to-[#F3D785] text-slate-900 border-amber-300/40 shadow-2xl shadow-amber-900/30';
            case 'bamboo_wood':
                return 'bg-gradient-to-tr from-[#4B3621] via-[#8B5A2B] to-[#C19A6B] text-white border-amber-800/40 shadow-2xl shadow-stone-900/30';
            case 'standard':
            default:
                return 'bg-gradient-to-tr from-[#6C5CE7] via-[#5244C5] to-[#14B8A6] text-white border-[#6C5CE7]/30 shadow-2xl shadow-[#6C5CE7]/20';
        }
    };

    return (
        <AppLayout title={t('my_card', 'My NFC Card')}>
            <Head title={t('my_card', 'My NFC Card')} />

            <div className="space-y-8">
                {/* Header Action Banner */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-[#141A2E] p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800/80">
                    <div className="flex items-center gap-4">
                        <div className="w-12 h-12 rounded-2xl bg-gradient-to-tr from-[#6C5CE7] to-[#14B8A6] text-white flex items-center justify-center shadow-lg shadow-[#6C5CE7]/20">
                            <Smartphone className="w-6 h-6" />
                        </div>
                        <div>
                            <h2 className="text-xl font-extrabold text-slate-900 dark:text-white">
                                {t('my_card', 'My NFC Card')}
                            </h2>
                            <p className="text-xs text-slate-500 dark:text-slate-400">
                                Real-time physical card inventory, status control, and hardware security
                            </p>
                        </div>
                    </div>

                    <Link href="/cards/claim">
                        <Button className="w-full sm:w-auto inline-flex items-center gap-2">
                            <Plus className="w-4 h-4" />
                            <span>{t('claim_card', 'Activate New Card')}</span>
                        </Button>
                    </Link>
                </div>

                {/* Empty State */}
                {cards.length === 0 ? (
                    <Card className="text-center py-16 px-4 space-y-4 max-w-xl mx-auto">
                        <div className="w-16 h-16 rounded-3xl bg-slate-100 dark:bg-[#1E2642] text-slate-400 flex items-center justify-center mx-auto">
                            <CreditCard className="w-8 h-8" />
                        </div>
                        <div className="space-y-1">
                            <h3 className="text-lg font-bold text-slate-900 dark:text-white">
                                No physical card linked yet
                            </h3>
                            <p className="text-xs text-slate-500 max-w-sm mx-auto">
                                Once you have received your physical AURA CARD, use the activation code inside your card packaging to link it to your account.
                            </p>
                        </div>
                        <div className="pt-2">
                            <Link href="/cards/claim">
                                <Button className="inline-flex items-center gap-2">
                                    <Sparkles className="w-4 h-4" />
                                    <span>Activate Your First Card</span>
                                </Button>
                            </Link>
                        </div>
                    </Card>
                ) : (
                    /* Cards Grid */
                    <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        {cards.map((card) => {
                            const isSuspended = card.status === 'suspended';
                            const isLost = card.status === 'lost';
                            const isReplaced = card.status === 'replaced';
                            const isDisabled = card.status === 'disabled';
                            const canToggle = card.status === 'active' || card.status === 'suspended';

                            return (
                                <div key={card.id} className="space-y-4">
                                    {/* Physical Simulation Card Display */}
                                    <div
                                        className={`relative aspect-[1.586/1] rounded-3xl p-6 flex flex-col justify-between overflow-hidden border backdrop-blur-sm transition-all duration-300 hover:scale-[1.02] ${getCardSkinClass(
                                            card.card_type
                                        )}`}
                                    >
                                        {/* Top Bar */}
                                        <div className="flex items-center justify-between z-10">
                                            <div className="flex items-center gap-2">
                                                <div className="w-8 h-6 rounded-md bg-amber-400/90 border border-amber-300/40 shadow-inner flex items-center justify-center">
                                                    <div className="w-5 h-4 border border-amber-600/40 rounded-xs" />
                                                </div>
                                                <span className="text-[10px] tracking-widest font-mono font-bold uppercase opacity-80">
                                                    NFC Chip
                                                </span>
                                            </div>

                                            <Badge
                                                variant={
                                                    card.status === 'active'
                                                        ? 'success'
                                                        : card.status === 'suspended'
                                                        ? 'warning'
                                                        : 'danger'
                                                }
                                                className="shadow-sm uppercase text-[10px] font-mono"
                                            >
                                                {card.status_label}
                                            </Badge>
                                        </div>

                                        {/* Center Branding & Serial */}
                                        <div className="z-10 space-y-1">
                                            <span className="text-lg font-black tracking-tight block">
                                                AURA<span className="opacity-80">CARD</span>
                                            </span>
                                            <span className="text-xs font-mono tracking-wider opacity-85 block truncate">
                                                Serial: {card.serial_number}
                                            </span>
                                        </div>

                                        {/* Bottom Details Bar */}
                                        <div className="flex items-end justify-between z-10 text-[10px] uppercase font-mono tracking-wider opacity-85">
                                            <div>
                                                <span className="block opacity-60 text-[8px]">Model</span>
                                                <span>{(card.card_type || 'Standard').replace('_', ' ')}</span>
                                            </div>
                                            <div className="text-end">
                                                <span className="block opacity-60 text-[8px]">Hardware Entitlement</span>
                                                <span className="flex items-center gap-1 font-sans font-bold">
                                                    <ShieldCheck className="w-3.5 h-3.5 text-emerald-300" />
                                                    {card.entitlement ? `${card.entitlement.initial_credits_amount} Credits Assigned` : 'Pending'}
                                                </span>
                                            </div>
                                        </div>

                                        {/* Watermark Logo */}
                                        <div className="absolute -bottom-8 -end-8 opacity-10 pointer-events-none">
                                            <Smartphone className="w-48 h-48" />
                                        </div>
                                    </div>

                                    {/* Management Actions Box */}
                                    <Card className="space-y-4">
                                        <div className="grid grid-cols-2 gap-2">
                                            {canToggle ? (
                                                <Button
                                                    variant="secondary"
                                                    size="sm"
                                                    onClick={() => handleToggleStatus(card.id)}
                                                    className="inline-flex items-center justify-center gap-1.5 text-xs"
                                                >
                                                    {isSuspended ? (
                                                        <>
                                                            <PlayCircle className="w-3.5 h-3.5 text-emerald-500" />
                                                            <span>Resume Tap</span>
                                                        </>
                                                    ) : (
                                                        <>
                                                            <PauseCircle className="w-3.5 h-3.5 text-amber-500" />
                                                            <span>Pause Tap</span>
                                                        </>
                                                    )}
                                                </Button>
                                            ) : (
                                                <Button
                                                    variant="secondary"
                                                    size="sm"
                                                    disabled
                                                    className="inline-flex items-center justify-center gap-1.5 text-xs opacity-50"
                                                >
                                                    <span>{card.status_label}</span>
                                                </Button>
                                            )}

                                            {!isLost && !isReplaced && !isDisabled ? (
                                                <Button
                                                    variant="secondary"
                                                    size="sm"
                                                    onClick={() => setConfirmingLostId(card.id)}
                                                    className="inline-flex items-center justify-center gap-1.5 text-xs text-rose-600 hover:text-rose-700"
                                                >
                                                    <AlertOctagon className="w-3.5 h-3.5 text-rose-500" />
                                                    <span>Report Lost</span>
                                                </Button>
                                            ) : (
                                                <div className="text-center text-xs text-slate-400 py-1.5">
                                                    Deactivated
                                                </div>
                                            )}
                                        </div>

                                        <div className="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-400">
                                            <span>
                                                Claimed:{' '}
                                                {card.claimed_at
                                                    ? new Date(card.claimed_at).toLocaleDateString()
                                                    : 'N/A'}
                                            </span>
                                            <span className="font-mono">
                                                {card.entitlement?.is_initial_credits_granted
                                                    ? 'Credits Dispensed'
                                                    : 'Credits Held Until First Publish'}
                                            </span>
                                        </div>
                                    </Card>

                                    {/* Confirm Lost Modal */}
                                    {confirmingLostId === card.id && (
                                        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
                                            <div className="bg-white dark:bg-[#141A2E] rounded-3xl max-w-md w-full p-6 space-y-4 border border-rose-200 dark:border-rose-900/50 shadow-2xl">
                                                <div className="flex items-center gap-3 text-rose-600">
                                                    <AlertOctagon className="w-6 h-6" />
                                                    <h3 className="font-extrabold text-base text-slate-900 dark:text-white">
                                                        Report Card Lost?
                                                    </h3>
                                                </div>

                                                <p className="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                                    Marking card <strong>{card.serial_number}</strong> as lost will immediately deactivate its public NFC routing. Your account and entitlement remain intact and can later be replaced with a replacement card by an administrator.
                                                </p>

                                                <div className="flex items-center justify-end gap-3 pt-2">
                                                    <Button
                                                        variant="secondary"
                                                        onClick={() => setConfirmingLostId(null)}
                                                    >
                                                        Cancel
                                                    </Button>
                                                    <Button
                                                        variant="danger"
                                                        onClick={() => handleReportLost(card.id)}
                                                    >
                                                        Confirm Lost
                                                    </Button>
                                                </div>
                                            </div>
                                        </div>
                                    )}
                                </div>
                            );
                        })}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
