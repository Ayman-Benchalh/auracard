import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import { AppLayout } from '@/Layouts/AppLayout';
import {
    Briefcase,
    Layout,
    Palette,
    User,
    FileText,
    Sparkles,
    CheckCircle2,
    ChevronRight,
    ChevronLeft,
    Phone,
    MapPin,
    Layers,
    Eye,
    Rocket,
    Globe,
    Check,
} from 'lucide-react';

interface ProfessionItem {
    id: string;
    slug: string;
    name: Record<string, string>;
    category?: string;
    icon?: string;
    default_blocks?: string[];
}

interface TemplateItem {
    id: string;
    slug: string;
    name: Record<string, string>;
    description?: Record<string, string>;
}

interface ThemeItem {
    id: string;
    slug: string;
    name: Record<string, string>;
    tokens: Record<string, string>;
}

interface EntitlementItem {
    id: string;
    card_serial?: string;
    card_type?: string;
}

interface OnboardingProps {
    entitlements: EntitlementItem[];
    professions: ProfessionItem[];
    templates: TemplateItem[];
    themes: ThemeItem[];
    defaultName: string;
    defaultEmail: string;
}

export default function Onboarding({
    entitlements,
    professions,
    templates,
    themes,
    defaultName,
    defaultEmail,
}: OnboardingProps) {
    const [step, setStep] = useState(1);
    const [searchProfession, setSearchProfession] = useState('');

    const { data, setData, post, processing, errors } = useForm({
        card_entitlement_id: entitlements[0]?.id || '',
        profession_type_id: professions[0]?.id || '',
        display_name: defaultName || '',
        headline: '',
        bio: '',
        company: '',
        job_title: '',
        location: 'Casablanca, Morocco',
        phone: '',
        email: defaultEmail || '',
        avatar_url: '',
        website: '',
        template_id: templates[0]?.id || '',
        theme_id: themes[0]?.id || '',
        primary_color: '#1e293b',
        secondary_color: '#3b82f6',
        font_family: 'Inter',
        enabled_modules: ['contact_actions', 'social_links', 'bio', 'services', 'vcard'],
        publish_now: true,
    });

    const filteredProfessions = professions.filter((p) => {
        const query = searchProfession.toLowerCase();
        return (
            (p.name?.en && p.name.en.toLowerCase().includes(query)) ||
            (p.name?.fr && p.name.fr.toLowerCase().includes(query)) ||
            (p.name?.ar && p.name.ar.toLowerCase().includes(query)) ||
            (p.category && p.category.toLowerCase().includes(query))
        );
    });

    const selectedTheme = themes.find((t) => t.id === data.theme_id);
    const selectedTemplate = templates.find((t) => t.id === data.template_id);
    const selectedProfession = professions.find((p) => p.id === data.profession_type_id);

    const handleNext = () => {
        if (step < 9) setStep(step + 1);
    };

    const handleBack = () => {
        if (step > 1) setStep(step - 1);
    };

    const submitWithPublish = (publishNow: boolean) => {
        setData('publish_now', publishNow);
        post('/profile');
    };

    const toggleModule = (modKey: string) => {
        const current = [...data.enabled_modules];
        const idx = current.indexOf(modKey);
        if (idx > -1) {
            current.splice(idx, 1);
        } else {
            current.push(modKey);
        }
        setData('enabled_modules', current);
    };

    const stepTitles = [
        '1. Choose Profession',
        '2. Identity & Card',
        '3. Professional Details',
        '4. Contact Information',
        '5. Modular Blocks',
        '6. Presentation Template',
        '7. Design Theme',
        '8. Interactive Preview',
        '9. Review & Publish',
    ];

    return (
        <AppLayout title="Create Your Digital Profile">
            <Head title="Profile Onboarding Wizard - AURA CARD" />

            <div className="max-w-4xl mx-auto space-y-8 py-4 px-4 sm:px-6">
                {/* Wizard Header */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200/80 dark:border-slate-800 pb-6">
                    <div className="space-y-1">
                        <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 text-xs font-bold">
                            <Sparkles className="w-3.5 h-3.5" />
                            <span>Step {step} of 9 — {stepTitles[step - 1]}</span>
                        </div>
                        <h1 className="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                            Digital Profile Setup Wizard
                        </h1>
                        <p className="text-xs text-slate-500">
                            Configure your physical NFC card identity in minutes. Complete onboarding and publish directly with 2 free bonus credits.
                        </p>
                    </div>

                    {/* Progress Bar */}
                    <div className="w-48 bg-slate-200 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden">
                        <div
                            className="bg-indigo-600 h-full transition-all duration-300"
                            style={{ width: `${(step / 9) * 100}%` }}
                        />
                    </div>
                </div>

                {/* Form Steps */}
                <form onSubmit={(e) => { e.preventDefault(); submitWithPublish(data.publish_now); }} className="space-y-8">
                    {/* STEP 1: PROFESSION */}
                    {step === 1 && (
                        <div className="space-y-4">
                            <div className="space-y-1">
                                <h2 className="text-lg font-bold text-slate-900 dark:text-white">1. Select Your Profession Archetype</h2>
                                <p className="text-xs text-slate-500">Pick the archetype that best matches your activity. We'll automatically seed the optimal modular blocks.</p>
                            </div>

                            <input
                                type="text"
                                placeholder="Search profession (e.g. Developer, Doctor, Architect, Restaurant, Lawyer)..."
                                value={searchProfession}
                                onChange={(e) => setSearchProfession(e.target.value)}
                                className="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 text-sm bg-white dark:bg-slate-900"
                            />

                            <div className="grid grid-cols-2 sm:grid-cols-3 gap-3 max-h-96 overflow-y-auto pr-1">
                                {filteredProfessions.map((prof) => (
                                    <button
                                        key={prof.id}
                                        type="button"
                                        onClick={() => setData('profession_type_id', prof.id)}
                                        className={`p-3.5 rounded-2xl border text-start transition-all flex flex-col justify-between gap-2 ${
                                            data.profession_type_id === prof.id
                                                ? 'border-indigo-600 bg-indigo-50/50 dark:bg-indigo-950/20 ring-2 ring-indigo-600/30'
                                                : 'border-slate-200 dark:border-slate-800 hover:border-slate-300'
                                        }`}
                                    >
                                        <div className="flex items-center justify-between">
                                            <span className="text-xl">{prof.icon || '💼'}</span>
                                            {data.profession_type_id === prof.id && (
                                                <CheckCircle2 className="w-4 h-4 text-indigo-600" />
                                            )}
                                        </div>
                                        <div>
                                            <h3 className="font-bold text-xs text-slate-900 dark:text-white">
                                                {prof.name?.en || prof.slug}
                                            </h3>
                                            <p className="text-[10px] text-slate-400 capitalize">
                                                {prof.category || 'Professional'}
                                            </p>
                                        </div>
                                    </button>
                                ))}
                            </div>
                        </div>
                    )}

                    {/* STEP 2: IDENTITY & CARD */}
                    {step === 2 && (
                        <div className="space-y-4">
                            <div className="space-y-1">
                                <h2 className="text-lg font-bold text-slate-900 dark:text-white">2. Identity & Card Binding</h2>
                                <p className="text-xs text-slate-500">Provide your primary public display name and choose which physical card to link.</p>
                            </div>

                            {entitlements.length > 1 && (
                                <div className="space-y-1">
                                    <label className="text-xs font-bold text-slate-700 dark:text-slate-300">Target Physical Card</label>
                                    <select
                                        value={data.card_entitlement_id}
                                        onChange={(e) => setData('card_entitlement_id', e.target.value)}
                                        className="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 text-sm bg-white dark:bg-slate-900"
                                    >
                                        {entitlements.map((e) => (
                                            <option key={e.id} value={e.id}>
                                                Card {e.card_serial} ({e.card_type})
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            )}

                            <div className="space-y-1">
                                <label className="text-xs font-bold text-slate-700 dark:text-slate-300">Display Name *</label>
                                <input
                                    type="text"
                                    required
                                    value={data.display_name}
                                    onChange={(e) => setData('display_name', e.target.value)}
                                    placeholder="e.g. Omar Bennani or Nova Tech SARL"
                                    className="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 text-sm bg-white dark:bg-slate-900"
                                />
                                {errors.display_name && <p className="text-xs text-red-500">{errors.display_name}</p>}
                            </div>

                            <div className="space-y-1">
                                <label className="text-xs font-bold text-slate-700 dark:text-slate-300">Avatar / Profile Picture URL (Optional)</label>
                                <input
                                    type="url"
                                    value={data.avatar_url || ''}
                                    onChange={(e) => setData('avatar_url', e.target.value)}
                                    placeholder="https://example.com/avatar.jpg"
                                    className="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 text-sm bg-white dark:bg-slate-900"
                                />
                            </div>
                        </div>
                    )}

                    {/* STEP 3: PROFESSIONAL DETAILS */}
                    {step === 3 && (
                        <div className="space-y-4">
                            <div className="space-y-1">
                                <h2 className="text-lg font-bold text-slate-900 dark:text-white">3. Professional Details & Bio</h2>
                                <p className="text-xs text-slate-500">Highlight your role, company, and concise biography for your visitors.</p>
                            </div>

                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div className="space-y-1">
                                    <label className="text-xs font-bold text-slate-700 dark:text-slate-300">Job Title / Specialty</label>
                                    <input
                                        type="text"
                                        value={data.job_title}
                                        onChange={(e) => {
                                            setData('job_title', e.target.value);
                                            setData('headline', e.target.value);
                                        }}
                                        placeholder="e.g. Senior Cardiologist, Full Stack Architect"
                                        className="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 text-sm bg-white dark:bg-slate-900"
                                    />
                                </div>

                                <div className="space-y-1">
                                    <label className="text-xs font-bold text-slate-700 dark:text-slate-300">Company / Organization</label>
                                    <input
                                        type="text"
                                        value={data.company}
                                        onChange={(e) => setData('company', e.target.value)}
                                        placeholder="e.g. Clinique Agdal, Casablanca FinTech Lab"
                                        className="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 text-sm bg-white dark:bg-slate-900"
                                    />
                                </div>
                            </div>

                            <div className="space-y-1">
                                <label className="text-xs font-bold text-slate-700 dark:text-slate-300">Biography / About</label>
                                <textarea
                                    rows={4}
                                    value={data.bio}
                                    onChange={(e) => setData('bio', e.target.value)}
                                    placeholder="Brief introduction highlighting your background, credentials, and offerings..."
                                    className="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 text-sm bg-white dark:bg-slate-900 leading-relaxed"
                                />
                            </div>
                        </div>
                    )}

                    {/* STEP 4: CONTACT INFORMATION */}
                    {step === 4 && (
                        <div className="space-y-4">
                            <div className="space-y-1">
                                <h2 className="text-lg font-bold text-slate-900 dark:text-white">4. Direct Contact Information</h2>
                                <p className="text-xs text-slate-500">Provide direct communication channels so tapping visitors can reach you instantly.</p>
                            </div>

                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div className="space-y-1">
                                    <label className="text-xs font-bold text-slate-700 dark:text-slate-300">Direct Phone / WhatsApp</label>
                                    <div className="relative">
                                        <Phone className="w-4 h-4 text-slate-400 absolute start-4 top-3.5" />
                                        <input
                                            type="tel"
                                            value={data.phone}
                                            onChange={(e) => setData('phone', e.target.value)}
                                            placeholder="+212 6 00 00 00 00"
                                            className="w-full ps-11 pe-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 text-sm bg-white dark:bg-slate-900"
                                        />
                                    </div>
                                </div>

                                <div className="space-y-1">
                                    <label className="text-xs font-bold text-slate-700 dark:text-slate-300">Location / City</label>
                                    <div className="relative">
                                        <MapPin className="w-4 h-4 text-slate-400 absolute start-4 top-3.5" />
                                        <input
                                            type="text"
                                            value={data.location}
                                            onChange={(e) => setData('location', e.target.value)}
                                            placeholder="Casablanca, Morocco"
                                            className="w-full ps-11 pe-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 text-sm bg-white dark:bg-slate-900"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* STEP 5: MODULES */}
                    {step === 5 && (
                        <div className="space-y-4">
                            <div className="space-y-1">
                                <h2 className="text-lg font-bold text-slate-900 dark:text-white">5. Modular Blocks Setup</h2>
                                <p className="text-xs text-slate-500">Select which blocks to include on your initial profile. You can reorder, add, or customize blocks anytime.</p>
                            </div>

                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                {[
                                    { key: 'contact_actions', label: '1-Click Contact Buttons', desc: 'Call, WhatsApp, Email, Maps shortcuts' },
                                    { key: 'social_links', label: 'Social Networks', desc: 'LinkedIn, X, Instagram, Facebook, GitHub' },
                                    { key: 'bio', label: 'About & Bio Card', desc: 'Expanded background and credentials' },
                                    { key: 'services', label: 'Services / Offerings', desc: 'Structured service cards with pricing' },
                                    { key: 'location', label: 'Interactive Map & Location', desc: 'Google Maps pin and directions' },
                                    { key: 'business_hours', label: 'Business Opening Hours', desc: 'Weekly schedule and status' },
                                    { key: 'vcard', label: 'Save Contact (vCard 4.0)', desc: '1-click direct contact download into phone address book' },
                                ].map((mod) => (
                                    <button
                                        key={mod.key}
                                        type="button"
                                        onClick={() => toggleModule(mod.key)}
                                        className={`p-4 rounded-2xl border text-start transition-all flex items-start gap-3 ${
                                            data.enabled_modules.includes(mod.key)
                                                ? 'border-indigo-600 bg-indigo-50/40 dark:bg-indigo-950/20 ring-2 ring-indigo-600/20'
                                                : 'border-slate-200 dark:border-slate-800 hover:border-slate-300'
                                        }`}
                                    >
                                        <div className={`w-5 h-5 rounded-md flex items-center justify-center mt-0.5 ${
                                            data.enabled_modules.includes(mod.key) ? 'bg-indigo-600 text-white' : 'border border-slate-300'
                                        }`}>
                                            {data.enabled_modules.includes(mod.key) && <Check className="w-3.5 h-3.5" />}
                                        </div>
                                        <div>
                                            <h4 className="text-xs font-bold text-slate-900 dark:text-white">{mod.label}</h4>
                                            <p className="text-[11px] text-slate-400">{mod.desc}</p>
                                        </div>
                                    </button>
                                ))}
                            </div>
                        </div>
                    )}

                    {/* STEP 6: TEMPLATE */}
                    {step === 6 && (
                        <div className="space-y-4">
                            <div className="space-y-1">
                                <h2 className="text-lg font-bold text-slate-900 dark:text-white">6. Presentation Layout Template</h2>
                                <p className="text-xs text-slate-500">Choose a structural layout for your public profile cards.</p>
                            </div>

                            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                {templates.map((tpl) => (
                                    <label
                                        key={tpl.id}
                                        className={`p-5 rounded-2xl border cursor-pointer transition-all flex flex-col justify-between ${
                                            data.template_id === tpl.id
                                                ? 'border-indigo-600 bg-indigo-50/50 dark:bg-indigo-950/20 ring-2 ring-indigo-600/30'
                                                : 'border-slate-200 dark:border-slate-800 hover:border-slate-300'
                                        }`}
                                    >
                                        <input
                                            type="radio"
                                            name="template_id"
                                            value={tpl.id}
                                            checked={data.template_id === tpl.id}
                                            onChange={(e) => setData('template_id', e.target.value)}
                                            className="sr-only"
                                        />
                                        <div className="space-y-2">
                                            <div className="flex items-center justify-between">
                                                <Layout className="w-5 h-5 text-indigo-600" />
                                                {data.template_id === tpl.id && <CheckCircle2 className="w-4 h-4 text-indigo-600" />}
                                            </div>
                                            <h3 className="font-bold text-sm text-slate-900 dark:text-white">
                                                {tpl.name?.en || tpl.slug}
                                            </h3>
                                            <p className="text-xs text-slate-400">
                                                {tpl.description?.en || 'Tailored design layout'}
                                            </p>
                                        </div>
                                    </label>
                                ))}
                            </div>
                        </div>
                    )}

                    {/* STEP 7: THEME */}
                    {step === 7 && (
                        <div className="space-y-4">
                            <div className="space-y-1">
                                <h2 className="text-lg font-bold text-slate-900 dark:text-white">7. Design Palette & Theme</h2>
                                <p className="text-xs text-slate-500">Select one of our 6 curated contrast-compliant color themes.</p>
                            </div>

                            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                {themes.map((th) => (
                                    <label
                                        key={th.id}
                                        className={`p-4 rounded-2xl border cursor-pointer transition-all flex flex-col gap-3 ${
                                            data.theme_id === th.id
                                                ? 'border-indigo-600 bg-indigo-50/50 dark:bg-indigo-950/20 ring-2 ring-indigo-600/30'
                                                : 'border-slate-200 dark:border-slate-800 hover:border-slate-300'
                                        }`}
                                    >
                                        <input
                                            type="radio"
                                            name="theme_id"
                                            value={th.id}
                                            checked={data.theme_id === th.id}
                                            onChange={(e) => {
                                                setData('theme_id', e.target.value);
                                                if (th.tokens?.primary) setData('primary_color', th.tokens.primary);
                                                if (th.tokens?.secondary) setData('secondary_color', th.tokens.secondary);
                                            }}
                                            className="sr-only"
                                        />
                                        <div className="flex items-center justify-between">
                                            <span className="font-bold text-xs text-slate-900 dark:text-white">{th.name?.en}</span>
                                            {data.theme_id === th.id && <CheckCircle2 className="w-4 h-4 text-indigo-600" />}
                                        </div>

                                        <div className="flex gap-1.5 h-6 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700">
                                            <div className="w-1/3 h-full" style={{ backgroundColor: th.tokens?.background || '#000' }} />
                                            <div className="w-1/3 h-full" style={{ backgroundColor: th.tokens?.primary || '#333' }} />
                                            <div className="w-1/3 h-full" style={{ backgroundColor: th.tokens?.secondary || '#666' }} />
                                        </div>
                                    </label>
                                ))}
                            </div>
                        </div>
                    )}

                    {/* STEP 8: LIVE INTERACTIVE PREVIEW */}
                    {step === 8 && (
                        <div className="space-y-4">
                            <div className="space-y-1">
                                <h2 className="text-lg font-bold text-slate-900 dark:text-white">8. Interactive Digital Card Preview</h2>
                                <p className="text-xs text-slate-500">Here is how your digital card will look when scanned or tapped on a smartphone.</p>
                            </div>

                            <div className="flex justify-center py-4">
                                <div className="w-full max-w-sm rounded-[40px] border-8 border-slate-900 dark:border-slate-800 bg-slate-950 p-4 shadow-2xl text-white space-y-4">
                                    <div className="text-center pt-4 space-y-2">
                                        <div className="w-20 h-20 rounded-full bg-gradient-to-tr from-indigo-500 to-teal-400 mx-auto flex items-center justify-center text-2xl font-bold text-white shadow-lg">
                                            {data.display_name.charAt(0).toUpperCase() || 'A'}
                                        </div>
                                        <h3 className="font-extrabold text-lg text-white">{data.display_name || 'Omar Bennani'}</h3>
                                        <p className="text-xs text-indigo-300">{data.headline || data.job_title || 'Professional Specialty'}</p>
                                        {data.company && <p className="text-[11px] text-slate-400">{data.company}</p>}
                                    </div>

                                    {data.bio && (
                                        <div className="p-3.5 rounded-2xl bg-white/5 border border-white/10 text-xs text-slate-300 leading-relaxed">
                                            {data.bio}
                                        </div>
                                    )}

                                    <div className="grid grid-cols-2 gap-2 text-xs">
                                        <div className="p-2.5 rounded-xl bg-indigo-600 text-center font-bold">
                                            Save Contact
                                        </div>
                                        <div className="p-2.5 rounded-xl bg-white/10 text-center font-bold">
                                            Exchange Info
                                        </div>
                                    </div>

                                    <div className="pt-2 text-center text-[10px] text-slate-500">
                                        Powered by AURA CARD • vCard 4.0
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* STEP 9: REVIEW & PUBLISH */}
                    {step === 9 && (
                        <div className="space-y-6">
                            <div className="space-y-1">
                                <h2 className="text-lg font-bold text-slate-900 dark:text-white">9. Ready to Launch Your AURA CARD</h2>
                                <p className="text-xs text-slate-500">
                                    Your profile is ready. Publishing now activates your physical card link immediately and grants +2 free modification credits.
                                </p>
                            </div>

                            <div className="p-6 rounded-3xl bg-gradient-to-br from-indigo-500/10 via-teal-500/10 to-indigo-500/5 border border-indigo-500/20 space-y-4">
                                <div className="flex items-center gap-3">
                                    <div className="w-10 h-10 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-bold">
                                        <Sparkles className="w-5 h-5" />
                                    </div>
                                    <div>
                                        <h4 className="font-bold text-sm text-slate-900 dark:text-white">First Publication Bonus</h4>
                                        <p className="text-xs text-slate-500">
                                            First publish costs <strong>0 credits</strong> and grants <strong>+2 free modification credits</strong> to your account!
                                        </p>
                                    </div>
                                </div>

                                <div className="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs pt-2 border-t border-indigo-500/10">
                                    <div>
                                        <span className="text-slate-400 block">Name</span>
                                        <span className="font-bold text-slate-800 dark:text-white">{data.display_name}</span>
                                    </div>
                                    <div>
                                        <span className="text-slate-400 block">Title</span>
                                        <span className="font-bold text-slate-800 dark:text-white">{data.job_title || data.headline || 'None'}</span>
                                    </div>
                                    <div>
                                        <span className="text-slate-400 block">Template</span>
                                        <span className="font-bold text-slate-800 dark:text-white">{selectedTemplate?.name?.en || 'Standard'}</span>
                                    </div>
                                    <div>
                                        <span className="text-slate-400 block">Theme</span>
                                        <span className="font-bold text-slate-800 dark:text-white">{selectedTheme?.name?.en || 'Standard'}</span>
                                    </div>
                                </div>
                            </div>

                            <div className="flex flex-col sm:flex-row items-center gap-4 pt-2">
                                <button
                                    type="button"
                                    disabled={processing}
                                    onClick={() => submitWithPublish(true)}
                                    className="w-full sm:w-auto flex-1 inline-flex items-center justify-center gap-2 px-8 py-4 rounded-2xl bg-gradient-to-r from-indigo-600 to-teal-500 text-white font-extrabold text-sm hover:opacity-95 shadow-xl shadow-indigo-600/30 transition-all"
                                >
                                    <Rocket className="w-5 h-5" />
                                    <span>Publish Digital Profile Now (0 credits • +2 Bonus)</span>
                                </button>

                                <button
                                    type="button"
                                    disabled={processing}
                                    onClick={() => submitWithPublish(false)}
                                    className="w-full sm:w-auto px-6 py-4 rounded-2xl border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs hover:bg-slate-50 dark:hover:bg-slate-900 transition-colors"
                                >
                                    <span>Save as Draft & Open Builder</span>
                                </button>
                            </div>
                        </div>
                    )}

                    {/* Navigation Buttons */}
                    <div className="flex items-center justify-between pt-6 border-t border-slate-200/80 dark:border-slate-800">
                        {step > 1 ? (
                            <button
                                type="button"
                                onClick={handleBack}
                                className="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800"
                            >
                                <ChevronLeft className="w-4 h-4" />
                                <span>Back</span>
                            </button>
                        ) : (
                            <div />
                        )}

                        {step < 9 && (
                            <button
                                type="button"
                                onClick={handleNext}
                                className="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-700 shadow-md shadow-indigo-600/20"
                            >
                                <span>Continue to {stepTitles[step]}</span>
                                <ChevronRight className="w-4 h-4" />
                            </button>
                        )}
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
