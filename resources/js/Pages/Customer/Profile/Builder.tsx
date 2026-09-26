import React, { useState } from 'react';
import { Head, router, useForm } from '@inertiajs/react';
import { AppLayout } from '@/Layouts/AppLayout';
import { BlockDispatcher, BlockData } from '@/Components/Profile/BlockDispatcher';
import {
    Sparkles,
    Eye,
    Save,
    Send,
    Plus,
    Trash2,
    MoveUp,
    MoveDown,
    ExternalLink,
    Palette,
    Layers,
    User,
    UploadCloud,
    Check,
    AlertCircle,
    Smartphone,
    CreditCard,
    ArrowUpRight,
} from 'lucide-react';

interface BuilderProps {
    profile: {
        id: string;
        slug: string;
        status: string;
        is_primary: boolean;
        version_counter: number;
        published_at?: string;
        display_name: string;
        headline?: string;
        bio?: string;
        company?: string;
        job_title?: string;
        location?: string;
        avatar_url?: string;
        cover_url?: string;
        profession_type_id?: string;
        template_id?: string;
        theme_id?: string;
        primary_color: string;
        secondary_color: string;
        font_family: string;
        custom_css?: string;
        blocks: BlockData[];
        media?: any[];
    };
    card?: {
        serial_number: string;
        card_type: string;
    } | null;
    editCreditBalance: number;
    isFirstPublish: boolean;
    professions: any[];
    templates: any[];
    themes: any[];
    publicUrl: string;
}

export default function Builder({
    profile,
    card,
    editCreditBalance,
    isFirstPublish,
    professions,
    templates,
    themes,
    publicUrl,
}: BuilderProps) {
    const [activeTab, setActiveTab] = useState<'general' | 'design' | 'blocks' | 'media'>('general');
    const [mobileView, setMobileView] = useState<'editor' | 'preview'>('editor');
    const [publishModalOpen, setPublishModalOpen] = useState(false);
    const [addBlockModalOpen, setAddBlockModalOpen] = useState(false);
    const [changeSummary, setChangeSummary] = useState('');
    const [isPublishing, setIsPublishing] = useState(false);

    // Draft form
    const { data, setData, put, processing, isDirty } = useForm({
        display_name: profile.display_name,
        slug: profile.slug,
        headline: profile.headline || '',
        bio: profile.bio || '',
        company: profile.company || '',
        job_title: profile.job_title || '',
        location: profile.location || '',
        template_id: profile.template_id || '',
        theme_id: profile.theme_id || '',
        primary_color: profile.primary_color || '#1e293b',
        secondary_color: profile.secondary_color || '#3b82f6',
        font_family: profile.font_family || 'Inter',
        custom_css: profile.custom_css || '',
    });

    const handleSaveDraft = (e?: React.FormEvent) => {
        if (e) e.preventDefault();
        put(`/profile/${profile.id}/draft`, {
            preserveScroll: true,
        });
    };

    const handlePublish = () => {
        setIsPublishing(true);
        router.post(
            `/profile/${profile.id}/publish`,
            { change_summary: changeSummary },
            {
                onFinish: () => {
                    setIsPublishing(false);
                    setPublishModalOpen(false);
                    setChangeSummary('');
                },
            }
        );
    };

    const handleMoveBlock = (index: number, direction: 'up' | 'down') => {
        const newBlocks = [...profile.blocks];
        const targetIndex = direction === 'up' ? index - 1 : index + 1;
        if (targetIndex < 0 || targetIndex >= newBlocks.length) return;

        const temp = newBlocks[index];
        newBlocks[index] = newBlocks[targetIndex];
        newBlocks[targetIndex] = temp;

        const orderedIds = newBlocks.map((b) => b.id);
        router.post(
            `/profile/${profile.id}/blocks/reorder`,
            { order: orderedIds },
            { preserveScroll: true }
        );
    };

    const handleDeleteBlock = (blockId: string) => {
        if (confirm('Are you sure you want to remove this modular block?')) {
            router.delete(`/profile/${profile.id}/blocks/${blockId}`, {
                preserveScroll: true,
            });
        }
    };

    const handleAddBlock = (type: string) => {
        let content: any = {};
        if (type === 'contact_actions') {
            content = {
                items: [
                    { type: 'phone', label: 'Call Me', value: '+212 600 000 000', is_primary: true },
                    { type: 'email', label: 'Email', value: 'hello@example.com' },
                ],
            };
        } else if (type === 'social_links') {
            content = {
                items: [
                    { platform: 'linkedin', url: 'https://linkedin.com', username: 'profile' },
                ],
            };
        } else if (type === 'services') {
            content = {
                items: [{ name: 'Executive Consultation', description: 'One-on-one advisory session', price: '$150' }],
            };
        } else if (type === 'bio') {
            content = { headline: 'About Me', bio: data.bio || 'Professional background summary' };
        }

        router.post(
            `/profile/${profile.id}/blocks`,
            {
                type,
                title: type.replace('_', ' ').toUpperCase(),
                is_visible: true,
                content,
                config: {},
            },
            {
                preserveScroll: true,
                onSuccess: () => setAddBlockModalOpen(false),
            }
        );
    };

    const handleFileUpload = (e: React.ChangeEvent<HTMLInputElement>, collection: 'avatar' | 'cover') => {
        if (!e.target.files || e.target.files.length === 0) return;
        const file = e.target.files[0];

        const formData = new FormData();
        formData.append('file', file);
        formData.append('collection', collection);

        fetch(`/profile/${profile.id}/media`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as any)?.content || '',
            },
        })
            .then((res) => res.json())
            .then((data) => {
                if (data.success && data.media?.url) {
                    if (collection === 'avatar') {
                        router.put(`/profile/${profile.id}/draft`, { avatar_url: data.media.url }, { preserveScroll: true });
                    } else {
                        router.put(`/profile/${profile.id}/draft`, { cover_url: data.media.url }, { preserveScroll: true });
                    }
                }
            });
    };

    return (
        <AppLayout title={`Edit Profile - ${data.display_name}`}>
            <Head title={`Builder: ${data.display_name} - AURA CARD`} />

            <div className="space-y-6">
                {/* Top Action Bar */}
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                    <div className="flex items-center gap-3">
                        <div className="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0">
                            <Sparkles className="w-6 h-6" />
                        </div>
                        <div>
                            <div className="flex items-center gap-2">
                                <h1 className="font-extrabold text-lg text-slate-900 dark:text-white">
                                    {data.display_name}
                                </h1>
                                <span
                                    className={`px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider ${
                                        profile.status === 'published'
                                            ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400'
                                            : 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-400'
                                    }`}
                                >
                                    {profile.status === 'published' ? `Live v${profile.version_counter}` : 'Draft Mode'}
                                </span>
                            </div>
                            <p className="text-xs text-slate-400">
                                Handle: <span className="font-mono text-slate-600 dark:text-slate-300">/p/{data.slug}</span>
                            </p>
                        </div>
                    </div>

                    <div className="flex flex-wrap items-center gap-2.5">
                        {/* Credits counter */}
                        <div className="px-3.5 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold flex items-center gap-2">
                            <Sparkles className="w-3.5 h-3.5 text-amber-500" />
                            <span>
                                <strong>{editCreditBalance}</strong> Credits Available
                            </span>
                        </div>

                        {/* Save Draft */}
                        <button
                            onClick={() => handleSaveDraft()}
                            disabled={processing}
                            className="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                        >
                            <Save className="w-4 h-4" />
                            <span>Save Draft</span>
                        </button>

                        {/* View Live */}
                        {profile.status === 'published' && (
                            <a
                                href={publicUrl}
                                target="_blank"
                                rel="noreferrer"
                                className="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                            >
                                <ExternalLink className="w-4 h-4" />
                                <span>View Public</span>
                            </a>
                        )}

                        {/* Publish */}
                        <button
                            onClick={() => setPublishModalOpen(true)}
                            className="inline-flex items-center gap-1.5 px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold shadow-md shadow-emerald-600/20 transition-all"
                        >
                            <Send className="w-4 h-4" />
                            <span>Publish Live</span>
                        </button>
                    </div>
                </div>

                {/* Mobile Tab Switcher */}
                <div className="flex lg:hidden rounded-2xl bg-slate-200 dark:bg-slate-800 p-1">
                    <button
                        onClick={() => setMobileView('editor')}
                        className={`flex-1 py-2 rounded-xl text-xs font-bold transition-all ${
                            mobileView === 'editor' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-xs' : 'text-slate-500'
                        }`}
                    >
                        Editor Panels
                    </button>
                    <button
                        onClick={() => setMobileView('preview')}
                        className={`flex-1 py-2 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 ${
                            mobileView === 'preview' ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-white shadow-xs' : 'text-slate-500'
                        }`}
                    >
                        <Smartphone className="w-3.5 h-3.5" />
                        <span>Interactive Preview</span>
                    </button>
                </div>

                {/* Split Pane Container */}
                <div className="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                    {/* LEFT PANE: Editor Tabs (7 cols) */}
                    <div className={`lg:col-span-7 space-y-6 ${mobileView === 'preview' ? 'hidden lg:block' : 'block'}`}>
                        {/* Tab Navigation */}
                        <div className="flex border-b border-slate-200 dark:border-slate-800 text-xs font-bold gap-4 sm:gap-6 overflow-x-auto whitespace-nowrap pb-0.5">
                            {[
                                { id: 'general', label: 'Identity & Info', icon: User },
                                { id: 'design', label: 'Theme & Style', icon: Palette },
                                { id: 'blocks', label: `Modular Blocks (${profile.blocks.length})`, icon: Layers },
                                { id: 'media', label: 'Photos & Assets', icon: UploadCloud },
                            ].map((tab) => {
                                const Icon = tab.icon;
                                return (
                                    <button
                                        key={tab.id}
                                        onClick={() => setActiveTab(tab.id as any)}
                                        className={`pb-3 flex items-center gap-2 border-b-2 transition-all shrink-0 ${
                                            activeTab === tab.id
                                                ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400'
                                                : 'border-transparent text-slate-400 hover:text-slate-600'
                                        }`}
                                    >
                                        <Icon className="w-4 h-4" />
                                        <span>{tab.label}</span>
                                    </button>
                                );
                            })}
                        </div>

                        {/* TAB 1: GENERAL INFO */}
                        {activeTab === 'general' && (
                            <form onSubmit={handleSaveDraft} className="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-4">
                                <div className="space-y-1">
                                    <label className="text-xs font-bold text-slate-700 dark:text-slate-300">Display Name</label>
                                    <input
                                        type="text"
                                        value={data.display_name}
                                        onChange={(e) => setData('display_name', e.target.value)}
                                        className="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm bg-white dark:bg-slate-800"
                                    />
                                </div>

                                <div className="space-y-1">
                                    <label className="text-xs font-bold text-slate-700 dark:text-slate-300">Headline / Title</label>
                                    <input
                                        type="text"
                                        value={data.headline}
                                        onChange={(e) => setData('headline', e.target.value)}
                                        className="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm bg-white dark:bg-slate-800"
                                    />
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div className="space-y-1">
                                        <label className="text-xs font-bold text-slate-700 dark:text-slate-300">Company</label>
                                        <input
                                            type="text"
                                            value={data.company}
                                            onChange={(e) => setData('company', e.target.value)}
                                            className="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm bg-white dark:bg-slate-800"
                                        />
                                    </div>
                                    <div className="space-y-1">
                                        <label className="text-xs font-bold text-slate-700 dark:text-slate-300">Location</label>
                                        <input
                                            type="text"
                                            value={data.location}
                                            onChange={(e) => setData('location', e.target.value)}
                                            className="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm bg-white dark:bg-slate-800"
                                        />
                                    </div>
                                </div>

                                <div className="space-y-1">
                                    <label className="text-xs font-bold text-slate-700 dark:text-slate-300">Bio</label>
                                    <textarea
                                        rows={4}
                                        value={data.bio}
                                        onChange={(e) => setData('bio', e.target.value)}
                                        className="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-sm bg-white dark:bg-slate-800"
                                    />
                                </div>

                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="px-5 py-2.5 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-xs font-bold"
                                >
                                    Save Draft Changes
                                </button>
                            </form>
                        )}

                        {/* TAB 2: DESIGN & THEMES */}
                        {activeTab === 'design' && (
                            <form onSubmit={handleSaveDraft} className="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-6">
                                {/* Themes */}
                                <div className="space-y-2">
                                    <label className="text-xs font-bold text-slate-700 dark:text-slate-300">Select Theme</label>
                                    <div className="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                        {themes.map((th) => (
                                            <div
                                                key={th.id}
                                                onClick={() => {
                                                    setData('theme_id', th.id);
                                                    if (th.tokens?.primary) setData('primary_color', th.tokens.primary);
                                                    if (th.tokens?.secondary) setData('secondary_color', th.tokens.secondary);
                                                }}
                                                className={`p-3 rounded-2xl border cursor-pointer text-xs font-bold transition-all ${
                                                    data.theme_id === th.id
                                                        ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-950/20 ring-2 ring-indigo-600/30'
                                                        : 'border-slate-200 dark:border-slate-700'
                                                }`}
                                            >
                                                <p className="truncate">{th.name?.en}</p>
                                                <div className="flex gap-1 h-3 rounded mt-2 overflow-hidden">
                                                    <div className="w-1/2" style={{ backgroundColor: th.tokens?.background }} />
                                                    <div className="w-1/2" style={{ backgroundColor: th.tokens?.primary }} />
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>

                                {/* Colors */}
                                <div className="grid grid-cols-2 gap-4">
                                    <div className="space-y-1">
                                        <label className="text-xs font-bold text-slate-700 dark:text-slate-300">Primary Color</label>
                                        <div className="flex items-center gap-2">
                                            <input
                                                type="color"
                                                value={data.primary_color}
                                                onChange={(e) => setData('primary_color', e.target.value)}
                                                className="w-9 h-9 rounded-lg cursor-pointer"
                                            />
                                            <input
                                                type="text"
                                                value={data.primary_color}
                                                onChange={(e) => setData('primary_color', e.target.value)}
                                                className="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-xs font-mono"
                                            />
                                        </div>
                                    </div>
                                    <div className="space-y-1">
                                        <label className="text-xs font-bold text-slate-700 dark:text-slate-300">Secondary Accent</label>
                                        <div className="flex items-center gap-2">
                                            <input
                                                type="color"
                                                value={data.secondary_color}
                                                onChange={(e) => setData('secondary_color', e.target.value)}
                                                className="w-9 h-9 rounded-lg cursor-pointer"
                                            />
                                            <input
                                                type="text"
                                                value={data.secondary_color}
                                                onChange={(e) => setData('secondary_color', e.target.value)}
                                                className="w-full px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 text-xs font-mono"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="px-5 py-2.5 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-xs font-bold"
                                >
                                    Apply Design Tokens
                                </button>
                            </form>
                        )}

                        {/* TAB 3: MODULAR BLOCKS */}
                        {activeTab === 'blocks' && (
                            <div className="space-y-4">
                                <div className="flex items-center justify-between">
                                    <h3 className="font-extrabold text-sm text-slate-900 dark:text-white">Active Profile Blocks</h3>
                                    <button
                                        onClick={() => setAddBlockModalOpen(true)}
                                        className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-700 transition-colors shadow-xs"
                                    >
                                        <Plus className="w-3.5 h-3.5" />
                                        <span>Add Modular Block</span>
                                    </button>
                                </div>

                                <div className="space-y-2.5">
                                    {profile.blocks.map((block, idx) => (
                                        <div
                                            key={block.id}
                                            className="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-between gap-4 shadow-xs"
                                        >
                                            <div className="flex items-center gap-3 min-w-0">
                                                <div className="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center text-xs font-mono font-bold flex-shrink-0">
                                                    {idx + 1}
                                                </div>
                                                <div className="min-w-0">
                                                    <p className="font-bold text-sm text-slate-900 dark:text-white truncate">
                                                        {block.title || block.type}
                                                    </p>
                                                    <span className="text-[10px] text-slate-400 capitalize">
                                                        {block.type.replace('_', ' ')}
                                                    </span>
                                                </div>
                                            </div>

                                            <div className="flex items-center gap-1">
                                                <button
                                                    onClick={() => handleMoveBlock(idx, 'up')}
                                                    disabled={idx === 0}
                                                    className="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 disabled:opacity-30"
                                                >
                                                    <MoveUp className="w-4 h-4" />
                                                </button>
                                                <button
                                                    onClick={() => handleMoveBlock(idx, 'down')}
                                                    disabled={idx === profile.blocks.length - 1}
                                                    className="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 disabled:opacity-30"
                                                >
                                                    <MoveDown className="w-4 h-4" />
                                                </button>
                                                <button
                                                    onClick={() => handleDeleteBlock(block.id)}
                                                    className="p-2 rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40"
                                                >
                                                    <Trash2 className="w-4 h-4" />
                                                </button>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        )}

                        {/* TAB 4: MEDIA ASSETS */}
                        {activeTab === 'media' && (
                            <div className="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-6">
                                <div className="space-y-2">
                                    <h4 className="text-xs font-bold text-slate-700 dark:text-slate-300">Avatar Image</h4>
                                    <div className="flex items-center gap-4">
                                        <div className="w-20 h-20 rounded-2xl bg-slate-100 dark:bg-slate-800 overflow-hidden border border-slate-200 dark:border-slate-700">
                                            {profile.avatar_url ? (
                                                <img src={profile.avatar_url} alt="Avatar" className="w-full h-full object-cover" />
                                            ) : (
                                                <div className="w-full h-full flex items-center justify-center text-slate-400 font-bold">
                                                    Avatar
                                                </div>
                                            )}
                                        </div>
                                        <label className="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-xs font-bold cursor-pointer">
                                            <span>Upload New Avatar</span>
                                            <input
                                                type="file"
                                                accept="image/jpeg,image/png,image/webp"
                                                onChange={(e) => handleFileUpload(e, 'avatar')}
                                                className="sr-only"
                                            />
                                        </label>
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <h4 className="text-xs font-bold text-slate-700 dark:text-slate-300">Cover Banner</h4>
                                    <div className="w-full h-32 rounded-2xl bg-slate-100 dark:bg-slate-800 overflow-hidden border border-slate-200 dark:border-slate-700 relative">
                                        {profile.cover_url ? (
                                            <img src={profile.cover_url} alt="Cover" className="w-full h-full object-cover" />
                                        ) : (
                                            <div className="w-full h-full flex items-center justify-center text-slate-400 text-xs font-bold">
                                                No Cover Image (Uses Gradient)
                                            </div>
                                        )}
                                    </div>
                                    <label className="inline-block px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-xs font-bold cursor-pointer">
                                        <span>Upload New Cover</span>
                                        <input
                                            type="file"
                                            accept="image/jpeg,image/png,image/webp"
                                            onChange={(e) => handleFileUpload(e, 'cover')}
                                            className="sr-only"
                                        />
                                    </label>
                                </div>
                            </div>
                        )}
                    </div>

                    {/* RIGHT PANE: Interactive Mobile Frame Preview (5 cols) */}
                    <div className={`lg:col-span-5 flex justify-center sticky top-24 ${mobileView === 'editor' ? 'hidden lg:flex' : 'flex'}`}>
                        <div className="w-[340px] h-[680px] rounded-[48px] p-3 bg-slate-900 border-[6px] border-slate-800 shadow-2xl overflow-hidden relative flex flex-col">
                            {/* Speaker notch */}
                            <div className="absolute top-4 left-1/2 -translate-x-1/2 w-24 h-4 bg-slate-800 rounded-full z-30" />

                            {/* Inner Screen */}
                            <div className="w-full h-full rounded-[38px] overflow-y-auto bg-slate-50 dark:bg-slate-950 text-slate-800 text-xs relative select-none">
                                {/* Cover Banner */}
                                <div
                                    className="h-28 w-full bg-gradient-to-tr"
                                    style={{
                                        backgroundImage: profile.cover_url
                                            ? `url(${profile.cover_url})`
                                            : `linear-gradient(135deg, ${data.primary_color}, ${data.secondary_color})`,
                                        backgroundSize: 'cover',
                                    }}
                                />

                                {/* Avatar & Info */}
                                <div className="px-4 pb-4 -mt-10 space-y-2">
                                    <div className="w-16 h-16 rounded-2xl bg-white p-0.5 shadow-md overflow-hidden">
                                        {profile.avatar_url ? (
                                            <img src={profile.avatar_url} alt="Avatar" className="w-full h-full object-cover rounded-xl" />
                                        ) : (
                                            <div
                                                className="w-full h-full rounded-xl flex items-center justify-center text-white font-bold"
                                                style={{ backgroundColor: data.primary_color }}
                                            >
                                                {data.display_name.charAt(0)}
                                            </div>
                                        )}
                                    </div>

                                    <div>
                                        <h2 className="font-extrabold text-sm text-slate-900 dark:text-white leading-tight">
                                            {data.display_name}
                                        </h2>
                                        <p className="text-[11px] text-slate-500 font-medium">{data.headline}</p>
                                        {data.company && <p className="text-[10px] text-slate-400">{data.company}</p>}
                                    </div>

                                    {/* Action button */}
                                    <div
                                        className="w-full py-2 rounded-xl text-center text-white font-bold text-xs shadow-md"
                                        style={{ backgroundColor: data.primary_color }}
                                    >
                                        Save to Contacts
                                    </div>
                                </div>

                                {/* Blocks */}
                                <div className="px-4 pb-8 space-y-4">
                                    {profile.blocks.map((block) => (
                                        <BlockDispatcher
                                            key={block.id}
                                            block={block}
                                            primaryColor={data.primary_color}
                                            secondaryColor={data.secondary_color}
                                        />
                                    ))}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Add Block Modal */}
            {addBlockModalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs">
                    <div className="w-full max-w-lg rounded-3xl bg-white dark:bg-slate-900 p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-5">
                        <div className="flex items-center justify-between">
                            <h3 className="font-extrabold text-base text-slate-900 dark:text-white">Add Modular Block</h3>
                            <button
                                onClick={() => setAddBlockModalOpen(false)}
                                className="text-slate-400 hover:text-slate-600 text-sm font-bold"
                            >
                                Cancel
                            </button>
                        </div>

                        <div className="grid grid-cols-2 gap-3 max-h-96 overflow-y-auto pr-1">
                            {[
                                { type: 'contact_actions', label: 'Contact Actions', desc: 'Call, WhatsApp, Email, Map' },
                                { type: 'social_links', label: 'Social Networks', desc: 'LinkedIn, X, Instagram' },
                                { type: 'services', label: 'Services List', desc: 'Prices & consultations' },
                                { type: 'portfolio', label: 'Portfolio Showcase', desc: 'Image gallery & links' },
                                { type: 'business_hours', label: 'Opening Hours', desc: 'Weekly opening schedule' },
                                { type: 'testimonials', label: 'Testimonials', desc: 'Client quotes & ratings' },
                                { type: 'booking', label: 'Appointment Booking', desc: 'Calendly or booking URL' },
                                { type: 'skills', label: 'Skills & Badges', desc: 'Core competences' },
                            ].map((item) => (
                                <button
                                    key={item.type}
                                    onClick={() => handleAddBlock(item.type)}
                                    className="p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 hover:border-indigo-600 hover:bg-indigo-50/50 dark:hover:bg-indigo-950/20 text-left transition-all space-y-1"
                                >
                                    <p className="font-bold text-xs text-slate-900 dark:text-white">{item.label}</p>
                                    <p className="text-[10px] text-slate-400">{item.desc}</p>
                                </button>
                            ))}
                        </div>
                    </div>
                </div>
            )}

            {/* Publish Modal */}
            {publishModalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs">
                    <div className="w-full max-w-md rounded-3xl bg-white dark:bg-slate-900 p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-5">
                        <div className="space-y-1">
                            <h3 className="font-extrabold text-lg text-slate-900 dark:text-white">Publish Live Version</h3>
                            <p className="text-xs text-slate-500">
                                This will take a cryptographic snapshot of your draft and update your live NFC card destination.
                            </p>
                        </div>

                        {/* Credit Rules Banner */}
                        <div
                            className={`p-4 rounded-2xl border ${
                                isFirstPublish
                                    ? 'bg-emerald-50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-800/40 text-emerald-900 dark:text-emerald-300'
                                    : 'bg-indigo-50 dark:bg-indigo-950/20 border-indigo-200 dark:border-indigo-800/40 text-indigo-900 dark:text-indigo-300'
                            }`}
                        >
                            <p className="font-extrabold text-xs">
                                {isFirstPublish ? '🎉 Initial Publication (Free)' : 'Standard Publication (1 Credit)'}
                            </p>
                            <p className="text-[11px] opacity-80 mt-1">
                                {isFirstPublish
                                    ? 'First publish consumes 0 credits and awards you +2 free modification credits for future updates!'
                                    : `This publication will deduct 1 credit from your balance (${editCreditBalance} credits remaining).`}
                            </p>
                        </div>

                        <div className="space-y-1">
                            <label className="text-xs font-bold text-slate-700 dark:text-slate-300">
                                Change Summary (Optional)
                            </label>
                            <input
                                type="text"
                                placeholder="e.g. Added WhatsApp direct contact and updated services"
                                value={changeSummary}
                                onChange={(e) => setChangeSummary(e.target.value)}
                                className="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs bg-white dark:bg-slate-800"
                            />
                        </div>

                        <div className="flex items-center justify-end gap-3 pt-2">
                            <button
                                onClick={() => setPublishModalOpen(false)}
                                className="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700"
                            >
                                Cancel
                            </button>
                            <button
                                onClick={handlePublish}
                                disabled={isPublishing || (!isFirstPublish && editCreditBalance < 1)}
                                className="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white text-xs font-extrabold shadow-md shadow-emerald-600/20"
                            >
                                {isPublishing ? 'Publishing...' : 'Confirm & Publish'}
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AppLayout>
    );
}
