import React, { useState } from 'react';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { AppLayout } from '@/Layouts/AppLayout';
import { Card } from '@/Components/UI/Card';
import { Badge } from '@/Components/UI/Badge';
import { Button } from '@/Components/UI/Button';
import { Input } from '@/Components/UI/Input';
import { useTranslation } from '@/Hooks/useTranslation';
import {
    CreditCard,
    Smartphone,
    Sparkles,
    Plus,
    Send,
    PauseCircle,
    PlayCircle,
    ExternalLink,
    AlertCircle,
    Copy,
    Check,
    X,
    ShieldCheck,
} from 'lucide-react';

interface CardItem {
    id: string;
    card_uid: string;
    status: 'inactive' | 'active' | 'suspended' | 'replaced' | 'retired';
    status_label: string;
    status_color: string;
    card_type: string;
    batch_number: string | null;
    claimed_at: string | null;
    entitlement: {
        initial_free_credits: number;
        credits_granted: number;
        is_claimed: boolean;
    } | null;
    pending_transfer: {
        id: string;
        to_email: string;
        transfer_token: string;
        expires_at: string;
    } | null;
}

interface CardsIndexProps {
    cards: CardItem[];
}

export default function CardsIndex({ cards }: CardsIndexProps) {
    const { t } = useTranslation();
    const [selectedCardForTransfer, setSelectedCardForTransfer] = useState<CardItem | null>(null);
    const [copiedToken, setCopiedToken] = useState<string | null>(null);

    const transferForm = useForm({
        to_email: '',
    });

    const handleTransferSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!selectedCardForTransfer) return;

        transferForm.post(`/cards/${selectedCardForTransfer.id}/transfer`, {
            onSuccess: () => {
                transferForm.reset();
                setSelectedCardForTransfer(null);
            },
        });
    };

    const handleToggleStatus = (cardId: string) => {
        router.patch(`/cards/${cardId}/toggle-status`, {}, {
            preserveScroll: true,
        });
    };

    const handleCancelTransfer = (transferId: string) => {
        router.delete(`/cards/transfers/${transferId}`, {
            preserveScroll: true,
        });
    };

    const handleCopy = (text: string, id: string) => {
        navigator.clipboard.writeText(text);
        setCopiedToken(id);
        setTimeout(() => setCopiedToken(null), 2000);
    };

    // Helper for visual card skins
    const getCardSkinClass = (cardType: string) => {
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
        <AppLayout title={t('my_card', 'My NFC Cards')}>
            <Head title={t('my_card', 'My NFC Cards')} />

            <div className="space-y-8">
                {/* Header Action Banner */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-[#141A2E] p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800/80">
                    <div className="flex items-center gap-4">
                        <div className="w-12 h-12 rounded-2xl bg-gradient-to-tr from-[#6C5CE7] to-[#14B8A6] text-white flex items-center justify-center shadow-lg shadow-[#6C5CE7]/20">
                            <Smartphone className="w-6 h-6" />
                        </div>
                        <div>
                            <h2 className="text-xl font-extrabold text-slate-900 dark:text-white">
                                {t('my_card', 'My NFC Cards')}
                            </h2>
                            <p className="text-xs text-slate-500 dark:text-slate-400">
                                Link physical hardware, pause routing, or transfer ownership with anti-fraud protection
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
                                No physical cards linked yet
                            </h3>
                            <p className="text-xs text-slate-500 max-w-sm mx-auto">
                                Once you have received your physical AURA CARD, use the activation code found inside the packaging to link it to your account.
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

                            return (
                                <div key={card.id} className="space-y-4">
                                    {/* Physical Card Simulation Display */}
                                    <div
                                        className={`relative aspect-[1.586/1] rounded-3xl p-6 flex flex-col justify-between overflow-hidden border backdrop-blur-sm transition-all duration-300 hover:scale-[1.02] ${getCardSkinClass(
                                            card.card_type
                                        )}`}
                                    >
                                        {/* Top Card Bar */}
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
                                                        : 'neutral'
                                                }
                                                className="shadow-sm"
                                            >
                                                {card.status_label}
                                            </Badge>
                                        </div>

                                        {/* Center Branding */}
                                        <div className="z-10 space-y-1">
                                            <span className="text-lg font-black tracking-tight block">
                                                AURA<span className="opacity-80">CARD</span>
                                            </span>
                                            <span className="text-[11px] font-mono tracking-widest opacity-80 block truncate">
                                                UID: {card.card_uid}
                                            </span>
                                        </div>

                                        {/* Bottom Details Bar */}
                                        <div className="flex items-end justify-between z-10 text-[10px] uppercase font-mono tracking-wider opacity-85">
                                            <div>
                                                <span className="block opacity-60 text-[8px]">Type</span>
                                                <span>{card.card_type.replace('_', ' ')}</span>
                                            </div>
                                            <div className="text-end">
                                                <span className="block opacity-60 text-[8px]">Entitlement</span>
                                                <span className="flex items-center gap-1 font-sans font-bold">
                                                    <ShieldCheck className="w-3.5 h-3.5 text-emerald-300" />
                                                    {card.entitlement?.initial_free_credits ?? 2} Credits
                                                </span>
                                            </div>
                                        </div>

                                        {/* Watermark Background NFC Waves */}
                                        <div className="absolute -bottom-8 -end-8 opacity-10 pointer-events-none">
                                            <Smartphone className="w-48 h-48" />
                                        </div>
                                    </div>

                                    {/* Management Card Actions Box */}
                                    <Card className="space-y-4">
                                        {/* Pending Transfer Alert if any */}
                                        {card.pending_transfer && (
                                            <div className="p-3 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-xs space-y-2">
                                                <div className="flex items-center justify-between text-amber-700 dark:text-amber-300 font-bold">
                                                    <span className="flex items-center gap-1.5">
                                                        <AlertCircle className="w-4 h-4" />
                                                        Pending Transfer
                                                    </span>
                                                    <button
                                                        onClick={() => handleCancelTransfer(card.pending_transfer!.id)}
                                                        className="text-rose-600 hover:underline font-semibold"
                                                    >
                                                        Cancel
                                                    </button>
                                                </div>
                                                <p className="text-slate-600 dark:text-slate-300">
                                                    Designated to: <span className="font-semibold">{card.pending_transfer.to_email}</span>
                                                </p>
                                                <div className="flex items-center gap-2 pt-1">
                                                    <input
                                                        type="text"
                                                        readOnly
                                                        value={card.pending_transfer.transfer_token}
                                                        className="text-[11px] font-mono bg-white dark:bg-[#141A2E] px-2 py-1 rounded border border-amber-400/40 w-full"
                                                    />
                                                    <button
                                                        onClick={() =>
                                                            handleCopy(
                                                                card.pending_transfer!.transfer_token,
                                                                card.pending_transfer!.id
                                                            )
                                                        }
                                                        className="p-1 rounded bg-amber-500 text-white hover:bg-amber-600"
                                                        title="Copy Token"
                                                    >
                                                        {copiedToken === card.pending_transfer.id ? (
                                                            <Check className="w-3.5 h-3.5" />
                                                        ) : (
                                                            <Copy className="w-3.5 h-3.5" />
                                                        )}
                                                    </button>
                                                </div>
                                            </div>
                                        )}

                                        {/* Action buttons */}
                                        <div className="grid grid-cols-2 gap-2">
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

                                            <Button
                                                variant="secondary"
                                                size="sm"
                                                onClick={() => setSelectedCardForTransfer(card)}
                                                disabled={Boolean(card.pending_transfer)}
                                                className="inline-flex items-center justify-center gap-1.5 text-xs"
                                            >
                                                <Send className="w-3.5 h-3.5 text-[#6C5CE7]" />
                                                <span>Transfer</span>
                                            </Button>
                                        </div>

                                        <div className="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-400">
                                            <span>
                                                Claimed:{' '}
                                                {card.claimed_at
                                                    ? new Date(card.claimed_at).toLocaleDateString()
                                                    : 'N/A'}
                                            </span>
                                            <a
                                                href={`/c/${encodeURIComponent(card.card_uid)}`}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="inline-flex items-center gap-1 text-[#6C5CE7] hover:underline font-semibold"
                                            >
                                                <span>Simulate Tap</span>
                                                <ExternalLink className="w-3 h-3" />
                                            </a>
                                        </div>
                                    </Card>
                                </div>
                            );
                        })}
                    </div>
                )}

                {/* Transfer Ownership Modal Dialog */}
                {selectedCardForTransfer && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
                        <div className="bg-white dark:bg-[#141A2E] rounded-3xl max-w-md w-full p-6 space-y-5 border border-slate-200 dark:border-slate-800 shadow-2xl">
                            <div className="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                                <div className="flex items-center gap-2">
                                    <Send className="w-5 h-5 text-[#6C5CE7]" />
                                    <h3 className="font-extrabold text-base text-slate-900 dark:text-white">
                                        Transfer Card Ownership
                                    </h3>
                                </div>
                                <button
                                    onClick={() => setSelectedCardForTransfer(null)}
                                    className="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-white"
                                >
                                    <X className="w-5 h-5" />
                                </button>
                            </div>

                            <p className="text-xs text-slate-500 dark:text-slate-400">
                                Transferring this card (UID: <code className="font-mono text-[#6C5CE7]">{selectedCardForTransfer.card_uid}</code>) will reassign physical ownership to the recipient. Note that initial publication credits remain consumed per anti-fraud rules.
                            </p>

                            <form onSubmit={handleTransferSubmit} className="space-y-4">
                                <Input
                                    label="Recipient Email Address"
                                    type="email"
                                    placeholder="recipient@example.com"
                                    value={transferForm.data.to_email}
                                    onChange={(e) => transferForm.setData('to_email', e.target.value)}
                                    error={transferForm.errors.to_email}
                                    required
                                />

                                <div className="flex items-center justify-end gap-3 pt-2">
                                    <Button
                                        type="button"
                                        variant="secondary"
                                        onClick={() => setSelectedCardForTransfer(null)}
                                    >
                                        Cancel
                                    </Button>
                                    <Button
                                        type="submit"
                                        isLoading={transferForm.processing}
                                        disabled={transferForm.processing}
                                    >
                                        Initiate Transfer
                                    </Button>
                                </div>
                            </form>
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
