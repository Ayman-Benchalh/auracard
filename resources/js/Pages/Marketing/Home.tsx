import React, { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import { MarketingLayout } from '@/Layouts/MarketingLayout';
import { Button } from '@/Components/UI/Button';
import { Card } from '@/Components/UI/Card';
import { Badge } from '@/Components/UI/Badge';
import { useTranslation } from '@/Hooks/useTranslation';
import {
    CreditCard,
    Smartphone,
    Sparkles,
    Shield,
    ShieldCheck,
    ArrowRight,
    Check,
    Zap,
    Download,
    Users,
    Briefcase,
    Building2,
    Eye,
    ChevronDown,
    Lock,
    QrCode,
    Sliders,
    PhoneCall,
    Share2,
    Star,
    Layers,
    Award,
    CheckCircle2,
    Wifi,
    ExternalLink,
} from 'lucide-react';

interface PackageData {
    id: string;
    key: string;
    name: any;
    description: any;
    price: number;
    currency: string;
    duration_months: number;
    physical_cards_included: number;
    profiles_included: number;
    edit_credits_per_profile: number;
    allows_customer_owned_card: boolean;
    requires_customer_owned_card: boolean;
    is_featured: boolean;
    features_list: any;
}

interface CardDesignData {
    id: string;
    key: string;
    name: any;
    description: any;
    material: string;
    color: string;
    preview_image: string | null;
}

interface HomeProps {
    packages: PackageData[];
    cardDesigns: CardDesignData[];
}

export default function Home({ packages = [], cardDesigns = [] }: HomeProps) {
    const { t, isRtl, locale } = useTranslation();
    const [activeDesignKey, setActiveDesignKey] = useState<string>(cardDesigns[0]?.key || 'matte_black');
    const [faqOpenIndex, setFaqOpenIndex] = useState<number | null>(null);

    const getLocalized = (field: any, defaultStr: string = '') => {
        if (!field) return defaultStr;
        if (typeof field === 'string') return field;
        return field[locale] || field['fr'] || field['en'] || defaultStr;
    };

    const getCardStyle = (key: string) => {
        switch (key) {
            case 'metal_gold':
            case '24k_gold':
                return {
                    name: '24K Gold Plated',
                    bg: 'bg-gradient-to-br from-[#854D0E] via-[#CA8A04] to-[#FEF08A]',
                    border: 'border-amber-400/80',
                    chip: 'from-amber-200 via-yellow-400 to-amber-600',
                    text: 'text-amber-950',
                    subText: 'text-amber-900/80',
                    badge: 'bg-amber-950/20 text-amber-950 border-amber-950/20',
                    swatch: 'bg-gradient-to-br from-amber-400 to-yellow-600',
                };
            case 'stainless_steel':
            case 'brushed_steel':
                return {
                    name: 'Brushed Steel',
                    bg: 'bg-gradient-to-br from-slate-600 via-slate-400 to-slate-200',
                    border: 'border-slate-300',
                    chip: 'from-amber-200 via-amber-400 to-amber-600',
                    text: 'text-slate-900',
                    subText: 'text-slate-800/80',
                    badge: 'bg-slate-900/20 text-slate-900 border-slate-900/20',
                    swatch: 'bg-gradient-to-br from-slate-300 to-slate-600',
                };
            case 'bamboo_wood':
                return {
                    name: 'Natural Bamboo',
                    bg: 'bg-gradient-to-br from-[#451A03] via-[#78350F] to-[#92400E]',
                    border: 'border-amber-800/80',
                    chip: 'from-amber-200 via-yellow-400 to-amber-600',
                    text: 'text-amber-50',
                    subText: 'text-amber-200/80',
                    badge: 'bg-black/30 text-amber-100 border-amber-500/30',
                    swatch: 'bg-gradient-to-br from-amber-800 to-yellow-900',
                };
            case 'aura_gradient':
                return {
                    name: 'Aura Prism',
                    bg: 'bg-gradient-to-br from-[#4F46E5] via-[#7C3AED] to-[#06B6D4]',
                    border: 'border-indigo-400/60',
                    chip: 'from-amber-200 via-yellow-400 to-amber-600',
                    text: 'text-white',
                    subText: 'text-slate-200',
                    badge: 'bg-white/20 text-white border-white/30',
                    swatch: 'bg-gradient-to-br from-indigo-500 via-purple-500 to-cyan-400',
                };
            case 'matte_black':
            default:
                return {
                    name: 'Matte Black PVC',
                    bg: 'bg-gradient-to-br from-[#090D16] via-[#111827] to-[#1E293B]',
                    border: 'border-slate-700/80',
                    chip: 'from-amber-200 via-yellow-400 to-amber-600',
                    text: 'text-white',
                    subText: 'text-slate-300',
                    badge: 'bg-white/10 text-slate-200 border-white/15',
                    swatch: 'bg-gradient-to-br from-slate-900 to-slate-800',
                };
        }
    };

    const activeStyle = getCardStyle(activeDesignKey);

    const professions = [
        {
            title: locale === 'ar' ? 'المحامون والمهن القانونية' : locale === 'en' ? 'Lawyers & Legal Counsel' : 'Avocats & Conseils Juridiques',
            desc: locale === 'ar' ? 'هوية رقمية رصينة ومطابقة لأخلاقيات المهنة وسرية الموكلين مع حفظ فوري لبيانات الاتصال.' : locale === 'en' ? 'Dignified, confidential digital presence strictly compliant with legal ethics and bar standards.' : 'Une présence sobre et prestigieuse, conforme à la déontologie et garantissant la confidentialité des échanges.',
            badge: locale === 'ar' ? 'سرية وأناقة' : locale === 'en' ? 'Discretion & Ethics' : 'Déontologie & Rigueur',
            icon: Award,
        },
        {
            title: locale === 'ar' ? 'الأطباء والمهن الصحية' : locale === 'en' ? 'Doctors & Healthcare' : 'Médecins & Spécialistes',
            desc: locale === 'ar' ? 'مشاركة عنوان العيادة عبر خرائط Google، أرقام الطوارئ، ورابط حجز المواعيد بنقرة واحدة.' : locale === 'en' ? 'Direct clinic location via GPS, emergency lines, and online appointment booking.' : 'Localisation précise du cabinet, permanence téléphonique et prise de rendez-vous fluide.',
            badge: locale === 'ar' ? 'حجز سريع' : locale === 'en' ? 'Direct Care' : 'Prise de RDV',
            icon: ShieldCheck,
        },
        {
            title: locale === 'ar' ? 'المهندسون والمعماريون' : locale === 'en' ? 'Architects & Engineers' : 'Architectes & Ingénieurs',
            desc: locale === 'ar' ? 'استعراض المشاريع المنجزة، المخططات الهندسية، والكتالوجات بصور عالية الدقة.' : locale === 'en' ? 'Showcase high-resolution architectural projects, technical portfolios, and blueprints.' : 'Présentation immersive de vos réalisations, maquettes 3D et portfolios haute résolution.',
            badge: locale === 'ar' ? 'معرض أعمال' : locale === 'en' ? 'Portfolio' : 'Portfolio HD',
            icon: Layers,
        },
        {
            title: locale === 'ar' ? 'الوكلاء العقاريون' : locale === 'en' ? 'Real Estate Brokers' : 'Agents Immobiliers & Promoteurs',
            desc: locale === 'ar' ? 'روابط العروض الحصرية، محادثة فورية عبر واتساب، وتنزيل فوري لبطاقة جهة الاتصال.' : locale === 'en' ? 'Direct links to active properties, instant WhatsApp chat, and immediate vCard sync.' : 'Partage instantané des mandats exclusifs, chat direct WhatsApp et contact vCard certifié.',
            badge: locale === 'ar' ? 'عروض حصرية' : locale === 'en' ? 'Fast Lead' : 'Lead Immédiat',
            icon: Building2,
        },
        {
            title: locale === 'ar' ? 'المدراء والتنفيذيون' : locale === 'en' ? 'Executives & Founders' : 'Dirigeants & Fondateurs',
            desc: locale === 'ar' ? 'بطاقة ميتاليك فاخرة تترك أثراً استثنائياً في المؤتمرات والاجتماعات الاستراتيجية.' : locale === 'en' ? 'Executive metallic finish leaving a decisive impression in high-stakes meetings.' : 'Carte métal de prestige pour marquer les esprits lors de comités et sommets stratégiques.',
            badge: locale === 'ar' ? 'انطباع حاسم' : locale === 'en' ? 'Executive Tier' : 'Édition Prestige',
            icon: Users,
        },
        {
            title: locale === 'ar' ? 'الاستشاريون والخبراء' : locale === 'en' ? 'Consultants & Advisors' : 'Consultants & Experts',
            desc: locale === 'ar' ? 'عرض السيرة المهنية، العروض التقديمية، وحجز جلسات الاستشارة عن بعد بكل سهولة.' : locale === 'en' ? 'Share credentials, methodology pitch decks, and discovery calendar sessions.' : 'Partagez vos références, plaquettes d’intervention et liens de réservation Calendly.',
            badge: locale === 'ar' ? 'استشارات رقمية' : locale === 'en' ? 'Discovery Call' : 'Expertise & RDV',
            icon: Briefcase,
        },
    ];

    const faqs = [
        {
            q: locale === 'ar' ? 'هل يحتاج الطرف الآخر إلى تطبيق خاص لقراءة بطاقتي؟' : locale === 'en' ? 'Does the other person need an app to read my card?' : 'La personne qui reçoit ma carte a-t-elle besoin d\'une application ?',
            a: locale === 'ar' ? 'إطلاقاً! لا يتطلب الأمر أي تطبيق. يكفي تمرير بطاقتك الذكية على أي هاتف ذكي (آيفون أو أندرويد) ليفتح الملف التعريفي الخاص بك فوراً في المتصفح، أو مسح رمز QR البديل.' : locale === 'en' ? 'No application is required. Simply tap your smart card against any smartphone (iPhone or Android) and your profile opens instantly in their browser.' : 'Absolument pas ! Aucune application n\'est requise. Un simple contact (tap) NFC avec un smartphone récent (iPhone ou Android) ouvre instantanément votre profil dans le navigateur. Un QR code sécurisé est également présent au dos.',
        },
        {
            q: locale === 'ar' ? 'ما هي الهواتف المتوافقة مع تقنية NFC؟' : locale === 'en' ? 'Which smartphones are compatible with NFC?' : 'Quels smartphones sont compatibles ?',
            a: locale === 'ar' ? 'جميع هواتف iPhone منذ موديل iPhone XR (2018) وحتى أحدث الموديلات، وأكثر من 95% من هواتف Android الحديثة تدعم تقنية NFC مسبقاً وبشكل تلقائي دون أي إعداد.' : locale === 'en' ? 'All iPhones from iPhone XR (2018) onward and over 95% of modern Android devices feature native NFC reading enabled by default.' : 'Pratiquement tous les smartphones modernes ! Tous les iPhone depuis l\'iPhone XR (2018) et plus de 95% des smartphones Android récents intègrent le NFC actif en continu.',
        },
        {
            q: locale === 'ar' ? 'كيف تعمل باقة "الملف الرقمي فقط" (100 درهم)؟' : locale === 'en' ? 'How does the Profile Only package (100 MAD) work?' : 'Comment fonctionne le forfait Profil Seul (100 MAD) ?',
            a: locale === 'ar' ? 'إذا كنت تمتلك بالفعل بطاقة NFC متوافقة أو ملصقاً ذكياً، تمنحك هذه الباقة اشتراكاً كاملاً لمدة 12 شهراً ورابط رمز تشفيري خاص يمكنك برمجته على بطاقتك مع 4 أرصدة تعديل.' : locale === 'en' ? 'If you already own a compatible writable NFC card or tag, this package grants a full 12-month entitlement and a cryptographically secure token link to program onto your tag, with 4 modification credits.' : 'Si vous possédez déjà une carte ou badge NFC inscriptible, cette offre vous donne accès à la plateforme pendant 12 mois avec 4 crédits de modification et un lien token chiffré prêt à être encodé sur votre support.',
        },
        {
            q: locale === 'ar' ? 'ماذا يحدث عند استهلاك جميع أرصدة التعديل المخصصة للملف؟' : locale === 'en' ? 'What happens when profile modification credits run out?' : 'Que se passe-t-il lorsque mes crédits de modification sont épuisés ?',
            a: locale === 'ar' ? 'يبقى ملفك منشوراً ومتاحاً للجميع طوال فترة اشتراكك السنوي دون انقطاع. يمكنك دائماً تعديل مسودة ملفك وحفظها، وتعبئة رصيد تعديلات إضافي فقط عند الرغبة في نشر التحديثات.' : locale === 'en' ? 'Your profile remains online and fully operational for the entire 12-month period. You can edit drafts anytime and top up additional modification credits when you wish to publish new revisions.' : 'Votre profil reste en ligne et fonctionnel sans interruption. Vous pouvez continuer à modifier votre brouillon à tout moment, et acquérir des recharges de crédits uniquement lorsque vous souhaitez publier une nouvelle version.',
        },
        {
            q: locale === 'ar' ? 'ما هي طرق الدفع المتاحة داخل المغرب؟' : locale === 'en' ? 'What payment methods are supported in Morocco?' : 'Quels sont les modes de paiement acceptés au Maroc ?',
            a: locale === 'ar' ? 'ندعم التحويل البنكي الوطني المباشر، أو الإيداع النقدي عبر شبكات وكالات الدفع المعتمدة في كافة المدن المغربية، مع تفعيل فوري للطلب فور مراجعة الإشعار.' : locale === 'en' ? 'We support standard national bank wire transfers and cash agency branch deposits across all Moroccan cities, with expedited activation upon slip upload.' : 'Nous acceptons les virements bancaires nationaux (RIB fourni au checkout) et les dépôts en espèces via les agences de paiement partenaires partout au Maroc, avec validation rapide dès réception du justificatif.',
        },
    ];

    return (
        <MarketingLayout>
            <Head title="AURACARD — Cartes de Visite Connectées NFC & Profils Professionnels au Maroc" />

            {/* SECTION 1: HERO SECTION */}
            <section className="relative overflow-hidden pt-8 pb-16 sm:pt-14 sm:pb-24 lg:pt-20 lg:pb-32">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                        {/* Hero Text Content */}
                        <div className="lg:col-span-7 space-y-6 text-center lg:text-start">
                            {/* Moroccan Tech Innovation Badge */}
                            <div className="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-900/5 dark:bg-white/10 text-slate-800 dark:text-slate-200 text-xs font-semibold border border-slate-200 dark:border-slate-800">
                                <span className="flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse" />
                                <span>{t('hero_badge')}</span>
                                <span className="text-slate-400">|</span>
                                <span className="text-[#6C5CE7] dark:text-[#818CF8] font-bold">Maroc</span>
                            </div>

                            {/* Main Headline */}
                            <h1 className="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight text-slate-900 dark:text-white leading-[1.12]">
                                {locale === 'ar' ? (
                                    <>
                                        بطاقة الأعمال الذكية <br />
                                        <span className="bg-gradient-to-r from-[#6C5CE7] via-[#818CF8] to-[#14B8A6] bg-clip-text text-transparent">
                                            التي تفتح كل الأبواب.
                                        </span>
                                    </>
                                ) : locale === 'en' ? (
                                    <>
                                        The Smart NFC Card <br />
                                        <span className="bg-gradient-to-r from-[#6C5CE7] via-[#818CF8] to-[#14B8A6] bg-clip-text text-transparent">
                                            that commands attention.
                                        </span>
                                    </>
                                ) : (
                                    <>
                                        La Carte de Visite Connectée <br />
                                        <span className="bg-gradient-to-r from-[#6C5CE7] via-[#818CF8] to-[#14B8A6] bg-clip-text text-transparent">
                                            qui marque les esprits.
                                        </span>
                                    </>
                                )}
                            </h1>

                            {/* Subtitle */}
                            <p className="text-base sm:text-lg text-slate-600 dark:text-slate-300 max-w-xl mx-auto lg:mx-0 font-normal leading-relaxed">
                                {locale === 'ar'
                                    ? 'شارك هويتك المهنية، بيانات الاتصال، وروابط أعمالك بلمسة NFC واحدة أو مسح QR. بدون تطبيق، بدون ورق، مع حماية تامة لبياناتك.'
                                    : locale === 'en'
                                    ? 'Laser-engraved physical NFC hardware paired with an advanced digital profile. Share contacts, services, and portfolios in a single tap without any app required.'
                                    : 'Partagez instantanément votre profil professionnel, vos coordonnées et votre catalogue d’un simple geste NFC. Zéro papier, zéro application, sécurisé et conçu pour durer.'}
                            </p>

                            {/* Action Buttons */}
                            <div className="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3.5 pt-2">
                                <a href="#pricing" className="w-full sm:w-auto">
                                    <Button
                                        variant="primary"
                                        size="lg"
                                        className="w-full sm:w-auto min-h-[48px] rounded-xl shadow-lg shadow-[#6C5CE7]/20 font-bold px-7"
                                    >
                                        <span>{t('order_your_card')}</span>
                                        <ArrowRight className={`w-4 h-4 ${isRtl ? 'rotate-180 me-2' : 'ms-2'}`} />
                                    </Button>
                                </a>

                                <a href="#demo-profile" className="w-full sm:w-auto">
                                    <Button
                                        variant="outline"
                                        size="lg"
                                        className="w-full sm:w-auto min-h-[48px] rounded-xl font-semibold px-6 border-slate-300 dark:border-slate-700"
                                    >
                                        <Eye className="w-4 h-4 me-2 text-[#6C5CE7] dark:text-[#818CF8]" />
                                        <span>{t('explore_live_demo')}</span>
                                    </Button>
                                </a>
                            </div>

                            {/* Trust Signals */}
                            <div className="pt-4 flex flex-wrap items-center justify-center lg:justify-start gap-5 text-xs text-slate-500 dark:text-slate-400 font-medium">
                                <div className="flex items-center gap-2">
                                    <CheckCircle2 className="w-4 h-4 text-emerald-500 shrink-0" />
                                    <span>Livraison express partout au Maroc</span>
                                </div>
                                <div className="flex items-center gap-2">
                                    <CheckCircle2 className="w-4 h-4 text-emerald-500 shrink-0" />
                                    <span>Paiement par virement ou espèces</span>
                                </div>
                                <div className="flex items-center gap-2">
                                    <CheckCircle2 className="w-4 h-4 text-emerald-500 shrink-0" />
                                    <span>Puce NFC certifiée ISO/IEC 14443</span>
                                </div>
                            </div>
                        </div>

                        {/* Interactive Hero Visual: Tactile Physical NFC Card */}
                        <div className="lg:col-span-5 flex flex-col items-center justify-center relative">
                            {/* The Physical Smart Card Mockup */}
                            <div
                                className={`w-full max-w-[380px] h-[236px] rounded-2xl p-6 relative flex flex-col justify-between border-2 shadow-2xl transition-all duration-500 select-none group ${activeStyle.bg} ${activeStyle.border}`}
                            >
                                {/* Specular sheen reflection overlay */}
                                <div className="absolute inset-0 rounded-2xl bg-gradient-to-tr from-white/10 via-transparent to-white/5 pointer-events-none" />

                                {/* Card Header: EMV Chip & Contactless Glyphs */}
                                <div className="flex items-center justify-between z-10">
                                    <div className="flex items-center gap-3">
                                        {/* Physical Chip Graphic */}
                                        <div
                                            className={`w-11 h-8 rounded-md bg-gradient-to-br ${activeStyle.chip} border border-amber-300/60 shadow-xs relative overflow-hidden flex items-center justify-center`}
                                        >
                                            <div className="w-full h-px bg-amber-900/40 absolute top-1/2" />
                                            <div className="h-full w-px bg-amber-900/40 absolute left-1/2" />
                                            <div className="w-3.5 h-3.5 rounded-full border border-amber-900/40" />
                                        </div>

                                        {/* Contactless Radio Waves */}
                                        <Wifi className="w-5 h-5 rotate-90 text-white/90" />
                                    </div>

                                    {/* Tap Notification Badge */}
                                    <span
                                        className={`px-2.5 py-0.5 rounded-full text-[10px] font-mono tracking-widest uppercase font-semibold border ${activeStyle.badge}`}
                                    >
                                        NTAG216
                                    </span>
                                </div>

                                {/* Card Center Holographic Monogram */}
                                <div className="flex items-center justify-center py-1 z-10">
                                    <div className="w-11 h-11 rounded-xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center shadow-inner">
                                        <Sparkles className="w-5 h-5 text-white/90" />
                                    </div>
                                </div>

                                {/* Card Footer: Cardholder & Brand */}
                                <div className="flex items-end justify-between z-10">
                                    <div>
                                        <div
                                            className={`text-[9px] tracking-widest font-mono uppercase font-semibold mb-0.5 ${activeStyle.subText}`}
                                        >
                                            Executive Edition
                                        </div>
                                        <div className={`text-sm font-bold tracking-wide ${activeStyle.text}`}>
                                            Yassine Bennani
                                        </div>
                                    </div>

                                    <div className="text-end">
                                        <div className={`text-base font-black tracking-widest leading-none ${activeStyle.text}`}>
                                            AURA<span className="text-[#14B8A6]">CARD</span>
                                        </div>
                                        <div className={`text-[9px] tracking-wider mt-0.5 ${activeStyle.subText}`}>
                                            Casablanca • Maroc
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Tactile Material Finishes Switcher */}
                            <div className="mt-6 flex items-center gap-2 p-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                                {cardDesigns.map((d) => {
                                    const style = getCardStyle(d.key);
                                    const isActive = activeDesignKey === d.key;
                                    return (
                                        <button
                                            key={d.key}
                                            type="button"
                                            onClick={() => setActiveDesignKey(d.key)}
                                            className={`flex items-center gap-2 px-3 py-1.5 min-h-[36px] rounded-lg text-xs font-semibold transition-all ${
                                                isActive
                                                    ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900 shadow-xs'
                                                    : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
                                            }`}
                                        >
                                            <span className={`w-3 h-3 rounded-full shrink-0 ${style.swatch}`} />
                                            <span>{getLocalized(d.name, d.key)}</span>
                                        </button>
                                    );
                                })}
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* SECTION 2: BENTO GRID ARCHITECTURE (Senior Product Showcase) */}
            <section id="features" className="py-20 bg-slate-50 dark:bg-[#0A0F1E] border-y border-slate-200/80 dark:border-slate-800/80 transition-colors">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                    <div className="text-center max-w-3xl mx-auto space-y-3">
                        <Badge variant="primary" className="px-3 py-1 text-xs uppercase font-bold tracking-wider">
                            Écosystème Physique &amp; Digital
                        </Badge>
                        <h2 className="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight">
                            Une technologie pensée pour les professionnels
                        </h2>
                        <p className="text-slate-600 dark:text-slate-400 text-sm sm:text-base">
                            Une carte physique taillée dans des matériaux d’exception, synchronisée en temps réel avec une suite de gestion complète.
                        </p>
                    </div>

                    {/* Bento Grid */}
                    <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-12 gap-6">
                        {/* Feature 1: Contactless NFC Tap (Large 7 cols) */}
                        <div className="lg:col-span-7 p-7 rounded-3xl bg-white dark:bg-[#11182A] border border-slate-200/90 dark:border-slate-800 shadow-sm flex flex-col justify-between space-y-6">
                            <div className="space-y-3">
                                <div className="w-10 h-10 rounded-xl bg-[#6C5CE7]/10 dark:bg-[#6C5CE7]/20 text-[#6C5CE7] dark:text-[#818CF8] flex items-center justify-center">
                                    <Zap className="w-5 h-5" />
                                </div>
                                <h3 className="text-xl font-bold text-slate-900 dark:text-white">
                                    Partage sans application (Zero-App Friction)
                                </h3>
                                <p className="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                                    Approchez votre carte du smartphone de votre interlocuteur. Ni vous ni votre contact n'avez besoin d'installer la moindre application. Le navigateur s'ouvre instantanément et propose d'enregistrer la fiche contact vCard 4.0 certifiée dans son carnet d'adresses en un clic.
                                </p>
                            </div>
                            <div className="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-4 border-t border-slate-100 dark:border-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300">
                                <div className="flex items-center gap-1.5">
                                    <Check className="w-4 h-4 text-emerald-500 shrink-0" />
                                    <span>iOS &amp; Android natif</span>
                                </div>
                                <div className="flex items-center gap-1.5">
                                    <Check className="w-4 h-4 text-emerald-500 shrink-0" />
                                    <span>vCard 4.0 standard</span>
                                </div>
                                <div className="flex items-center gap-1.5">
                                    <Check className="w-4 h-4 text-emerald-500 shrink-0" />
                                    <span>QR code de secours</span>
                                </div>
                            </div>
                        </div>

                        {/* Feature 2: Sovereign Data Security (5 cols) */}
                        <div className="lg:col-span-5 p-7 rounded-3xl bg-white dark:bg-[#11182A] border border-slate-200/90 dark:border-slate-800 shadow-sm flex flex-col justify-between space-y-6">
                            <div className="space-y-3">
                                <div className="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                    <ShieldCheck className="w-5 h-5" />
                                </div>
                                <h3 className="text-xl font-bold text-slate-900 dark:text-white">
                                    Sécurité &amp; Conformité CNDP
                                </h3>
                                <p className="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                                    Architecture anti-fraude basée sur des jetons cryptographiques infalsifiables. Zéro dépendance à l’UID matériel non sécurisé. Toutes les statistiques sont anonymisées avec salage HMAC.
                                </p>
                            </div>
                            <div className="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 text-xs text-slate-600 dark:text-slate-400 flex items-center gap-2">
                                <Lock className="w-4 h-4 text-[#6C5CE7] shrink-0" />
                                <span>Chiffrement SSL &amp; données souveraines au Maroc</span>
                            </div>
                        </div>

                        {/* Feature 3: Modular Profile Builder (5 cols) */}
                        <div className="lg:col-span-5 p-7 rounded-3xl bg-white dark:bg-[#11182A] border border-slate-200/90 dark:border-slate-800 shadow-sm flex flex-col justify-between space-y-6">
                            <div className="space-y-3">
                                <div className="w-10 h-10 rounded-xl bg-[#14B8A6]/10 text-[#14B8A6] flex items-center justify-center">
                                    <Sliders className="w-5 h-5" />
                                </div>
                                <h3 className="text-xl font-bold text-slate-900 dark:text-white">
                                    19 Blocs Modulaires Personnalisables
                                </h3>
                                <p className="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                                    Structurez votre page selon vos priorités : bouton WhatsApp direct, formulaire de capture de leads, calendrier de réservation, portfolio d'images, liens réseaux sociaux et catalogue de services.
                                </p>
                            </div>
                            <div className="flex flex-wrap gap-1.5 pt-2 text-[11px] font-medium text-slate-600 dark:text-slate-300">
                                <span className="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800">WhatsApp</span>
                                <span className="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800">Calendly</span>
                                <span className="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800">Lead Form</span>
                                <span className="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800">Catalogue</span>
                            </div>
                        </div>

                        {/* Feature 4: Controlled Edit Credits (7 cols) */}
                        <div className="lg:col-span-7 p-7 rounded-3xl bg-white dark:bg-[#11182A] border border-slate-200/90 dark:border-slate-800 shadow-sm flex flex-col justify-between space-y-6">
                            <div className="space-y-3">
                                <div className="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                                    <CreditCard className="w-5 h-5" />
                                </div>
                                <h3 className="text-xl font-bold text-slate-900 dark:text-white">
                                    Crédits de Modification &amp; Brouillons Illimités
                                </h3>
                                <p className="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                                    Modifiez et prévisualisez vos brouillons autant de fois que vous le souhaitez sans frais. Votre profil en ligne reste actif 24h/24 et 7j/7 pendant vos 12 mois de souscription. Un crédit n'est débité que lorsque vous décidez de publier une nouvelle version publique.
                                </p>
                            </div>
                            <div className="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-4 border-t border-slate-100 dark:border-slate-800 text-xs font-semibold text-slate-700 dark:text-slate-300">
                                <div className="flex items-center gap-1.5">
                                    <Check className="w-4 h-4 text-emerald-500 shrink-0" />
                                    <span>1ère publication gratuite</span>
                                </div>
                                <div className="flex items-center gap-1.5">
                                    <Check className="w-4 h-4 text-emerald-500 shrink-0" />
                                    <span>Brouillons illimités</span>
                                </div>
                                <div className="flex items-center gap-1.5">
                                    <Check className="w-4 h-4 text-emerald-500 shrink-0" />
                                    <span>Crédits isolés par profil</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* SECTION 3: INTERACTIVE DEMO VISITOR PREVIEW */}
            <section id="demo-profile" className="py-20 bg-white dark:bg-[#080D1A] transition-colors">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                        <div className="lg:col-span-6 space-y-6">
                            <Badge variant="primary" className="px-3 py-1 text-xs uppercase font-bold tracking-wider">
                                Expérience Visiteur Fluide
                            </Badge>
                            <h2 className="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight leading-tight">
                                Ce que vos contacts découvrent en scannant votre carte
                            </h2>
                            <p className="text-slate-600 dark:text-slate-300 text-sm sm:text-base leading-relaxed">
                                Dès que votre carte est scannée, une page web responsive ultra-rapide s’affiche. Vos interlocuteurs peuvent immédiatement télécharger votre fiche contact, vous appeler ou vous envoyer un message WhatsApp sans aucune friction.
                            </p>

                            <div className="space-y-3.5 pt-2">
                                {[
                                    'Téléchargement instantané de vCard 4.0 certifiée',
                                    'Bouton WhatsApp direct pré-rempli',
                                    'Formulaire de capture de coordonnées dans votre dashboard',
                                    'Statistiques de consultation en temps réel anonymisées',
                                ].map((feature) => (
                                    <div key={feature} className="flex items-center gap-3 text-sm text-slate-700 dark:text-slate-300">
                                        <div className="w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                            <Check className="w-3.5 h-3.5" />
                                        </div>
                                        <span>{feature}</span>
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Interactive Phone Mockup */}
                        <div className="lg:col-span-6 flex justify-center">
                            <div className="w-full max-w-[320px] rounded-[40px] bg-slate-950 border-[6px] border-slate-800 p-4 shadow-2xl text-white space-y-4 relative">
                                {/* Speaker notch */}
                                <div className="w-20 h-3.5 rounded-full bg-slate-800 mx-auto" />

                                {/* Profile interior */}
                                <div className="rounded-2xl bg-slate-900 p-4 text-center space-y-3 border border-slate-800">
                                    <div className="w-16 h-16 rounded-full bg-gradient-to-tr from-[#6C5CE7] to-[#14B8A6] mx-auto flex items-center justify-center font-black text-xl text-white shadow-md">
                                        YB
                                    </div>
                                    <div>
                                        <h4 className="font-bold text-base text-white">Yassine Bennani</h4>
                                        <p className="text-xs text-[#818CF8] font-medium">Cabinet Bennani &amp; Associés</p>
                                        <p className="text-[11px] text-slate-400">Casablanca, Maroc</p>
                                    </div>

                                    {/* Action Buttons */}
                                    <div className="grid grid-cols-2 gap-2 pt-2">
                                        <div className="p-2.5 rounded-xl bg-[#6C5CE7] text-white text-xs font-semibold flex items-center justify-center gap-1.5 shadow-sm">
                                            <Download className="w-3.5 h-3.5" />
                                            <span>vCard</span>
                                        </div>
                                        <div className="p-2.5 rounded-xl bg-emerald-600 text-white text-xs font-semibold flex items-center justify-center gap-1.5 shadow-sm">
                                            <PhoneCall className="w-3.5 h-3.5" />
                                            <span>WhatsApp</span>
                                        </div>
                                    </div>
                                </div>

                                {/* Quick Links Preview */}
                                <div className="space-y-2">
                                    <div className="p-2.5 rounded-xl bg-slate-900/90 border border-slate-800 flex items-center justify-between text-xs">
                                        <span className="text-slate-300 font-medium">Portfolio &amp; Dossiers</span>
                                        <ArrowRight className="w-3.5 h-3.5 text-slate-500 rtl:rotate-180" />
                                    </div>
                                    <div className="p-2.5 rounded-xl bg-slate-900/90 border border-slate-800 flex items-center justify-between text-xs">
                                        <span className="text-slate-300 font-medium">Prendre RDV (Consultation)</span>
                                        <ArrowRight className="w-3.5 h-3.5 text-slate-500 rtl:rotate-180" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* SECTION 4: PROFESSIONS & USE CASES */}
            <section id="professions" className="py-20 bg-slate-50 dark:bg-[#0A0F1E] border-y border-slate-200/80 dark:border-slate-800/80 transition-colors">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                    <div className="text-center max-w-3xl mx-auto space-y-3">
                        <Badge variant="neutral" className="px-3 py-1 text-xs uppercase font-bold tracking-wider">
                            Cas d’usage métier
                        </Badge>
                        <h2 className="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight">
                            Adapté à chaque profession au Maroc
                        </h2>
                        <p className="text-slate-600 dark:text-slate-400 text-sm sm:text-base">
                            Des fonctionnalités pensées pour répondre aux exigences réelles des dirigeants et indépendants marocains.
                        </p>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {professions.map((prof) => {
                            const Icon = prof.icon;
                            return (
                                <div
                                    key={prof.title}
                                    className="p-6 rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#11182A] hover:border-slate-300 dark:hover:border-slate-700 shadow-sm hover:shadow-md transition-all space-y-4"
                                >
                                    <div className="flex items-center justify-between">
                                        <div className="w-10 h-10 rounded-xl bg-[#6C5CE7]/10 dark:bg-[#6C5CE7]/20 text-[#6C5CE7] dark:text-[#818CF8] flex items-center justify-center">
                                            <Icon className="w-5 h-5" />
                                        </div>
                                        <span className="text-[10px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                            {prof.badge}
                                        </span>
                                    </div>
                                    <h3 className="font-bold text-base text-slate-900 dark:text-white">
                                        {prof.title}
                                    </h3>
                                    <p className="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                        {prof.desc}
                                    </p>
                                </div>
                            );
                        })}
                    </div>
                </div>
            </section>

            {/* SECTION 5: REAL DATABASE COMMERCIAL PACKAGES */}
            <section id="pricing" className="py-20 bg-white dark:bg-[#080D1A] transition-colors">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                    <div className="text-center max-w-3xl mx-auto space-y-3">
                        <Badge variant="primary" className="px-3 py-1 text-xs uppercase font-bold tracking-wider">
                            Tarification Transparente
                        </Badge>
                        <h2 className="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight">
                            Des forfaits complets pour 12 mois
                        </h2>
                        <p className="text-slate-600 dark:text-slate-400 text-sm sm:text-base">
                            Tous nos forfaits incluent l’hébergement sécurisé, la première publication gratuite et l'assistance WhatsApp.
                        </p>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-stretch">
                        {packages.map((pkg) => {
                            const isFeatured = pkg.is_featured;
                            return (
                                <div
                                    key={pkg.id}
                                    className={`rounded-3xl p-6 flex flex-col justify-between transition-all duration-300 relative ${
                                        isFeatured
                                            ? 'bg-slate-50 dark:bg-[#141A2E] border-2 border-[#6C5CE7] shadow-xl shadow-[#6C5CE7]/15 ring-2 ring-[#6C5CE7]/20 scale-[1.02]'
                                            : 'bg-white dark:bg-[#111728] border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md'
                                    }`}
                                >
                                    {/* Featured Badge */}
                                    {isFeatured && (
                                        <div className="absolute -top-3.5 start-1/2 -translate-x-1/2">
                                            <span className="px-3.5 py-1 rounded-full bg-gradient-to-r from-[#6C5CE7] to-[#14B8A6] text-white text-[10px] font-black uppercase tracking-wider shadow-md">
                                                Recommandé
                                            </span>
                                        </div>
                                    )}

                                    <div className="space-y-4">
                                        <div>
                                            <h3 className="text-lg font-black text-slate-900 dark:text-white">
                                                {getLocalized(pkg.name, pkg.key)}
                                            </h3>
                                            <p className="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">
                                                {getLocalized(pkg.description)}
                                            </p>
                                        </div>

                                        {/* Price Strictly From Database */}
                                        <div className="pt-2 border-t border-slate-100 dark:border-slate-800">
                                            <div className="flex items-baseline gap-1">
                                                <span className="text-3xl font-black text-slate-900 dark:text-white">
                                                    {pkg.price}
                                                </span>
                                                <span className="text-sm font-bold text-slate-500 dark:text-slate-400">
                                                    {pkg.currency}
                                                </span>
                                                <span className="text-xs text-slate-400 ms-1 font-normal">
                                                    / {pkg.duration_months} mois
                                                </span>
                                            </div>
                                        </div>

                                        {/* Quotas */}
                                        <div className="flex flex-wrap gap-1.5 pt-1">
                                            <Badge variant="neutral" className="text-[10px]">
                                                {pkg.physical_cards_included} Carte{pkg.physical_cards_included > 1 ? 's' : ''}
                                            </Badge>
                                            <Badge variant="neutral" className="text-[10px]">
                                                {pkg.profiles_included} Profil{pkg.profiles_included > 1 ? 's' : ''}
                                            </Badge>
                                            <Badge variant="primary" className="text-[10px]">
                                                {pkg.edit_credits_per_profile} Crédits/profil
                                            </Badge>
                                        </div>

                                        {/* Features List */}
                                        <ul className="space-y-2 pt-2 text-xs text-slate-600 dark:text-slate-300">
                                            <li className="flex items-start gap-2">
                                                <Check className="w-3.5 h-3.5 text-emerald-500 mt-0.5 shrink-0" />
                                                <span>Première publication 100% offerte</span>
                                            </li>
                                            <li className="flex items-start gap-2">
                                                <Check className="w-3.5 h-3.5 text-emerald-500 mt-0.5 shrink-0" />
                                                <span>Téléchargement vCard 4.0 sans application</span>
                                            </li>
                                            <li className="flex items-start gap-2">
                                                <Check className="w-3.5 h-3.5 text-emerald-500 mt-0.5 shrink-0" />
                                                <span>Brouillons illimités toute l'année</span>
                                            </li>
                                            {pkg.physical_cards_included === 0 && (
                                                <li className="flex items-start gap-2 text-[#6C5CE7] dark:text-[#818CF8] font-semibold">
                                                    <Check className="w-3.5 h-3.5 mt-0.5 shrink-0" />
                                                    <span>Compatible avec votre propre carte NFC</span>
                                                </li>
                                            )}
                                        </ul>
                                    </div>

                                    {/* Direct Checkout CTA Button */}
                                    <div className="pt-6">
                                        <Link href={`/checkout/package/${pkg.id}`} className="block">
                                            <Button
                                                variant={isFeatured ? 'primary' : 'outline'}
                                                className={`w-full justify-center rounded-xl font-bold text-xs min-h-[44px] ${
                                                    isFeatured ? 'shadow-md shadow-[#6C5CE7]/20' : ''
                                                }`}
                                            >
                                                <span>Commander ({pkg.price} {pkg.currency})</span>
                                                <ArrowRight className={`w-3.5 h-3.5 ${isRtl ? 'rotate-180 me-1' : 'ms-1'}`} />
                                            </Button>
                                        </Link>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div>
            </section>

            {/* SECTION 6: FAQ ACCORDION */}
            <section id="faq" className="py-20 bg-slate-50 dark:bg-[#0A0F1E] border-t border-slate-200/80 dark:border-slate-800/80 transition-colors">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                    <div className="text-center space-y-3">
                        <Badge variant="neutral" className="px-3 py-1 text-xs uppercase font-bold tracking-wider">
                            Questions Fréquentes
                        </Badge>
                        <h2 className="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight">
                            Tout ce que vous devez savoir
                        </h2>
                    </div>

                    <div className="space-y-3.5">
                        {faqs.map((faq, index) => {
                            const isOpen = faqOpenIndex === index;
                            return (
                                <div
                                    key={index}
                                    className="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#11182A] overflow-hidden transition-colors"
                                >
                                    <button
                                        type="button"
                                        onClick={() => setFaqOpenIndex(isOpen ? null : index)}
                                        className="w-full p-5 text-start flex items-center justify-between gap-4 font-bold text-slate-900 dark:text-white text-sm sm:text-base hover:text-[#6C5CE7] transition-colors min-h-[48px]"
                                    >
                                        <span>{faq.q}</span>
                                        <ChevronDown className={`w-5 h-5 shrink-0 transition-transform ${isOpen ? 'rotate-180 text-[#6C5CE7]' : 'text-slate-400'}`} />
                                    </button>
                                    {isOpen && (
                                        <div className="px-5 pb-5 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100 dark:border-slate-800/60 pt-3">
                                            {faq.a}
                                        </div>
                                    )}
                                </div>
                            );
                        })}
                    </div>
                </div>
            </section>

            {/* SECTION 7: FINAL CALL TO ACTION */}
            <section className="py-20 bg-gradient-to-br from-[#6C5CE7] to-[#4F46E5] text-white text-center relative overflow-hidden">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 relative z-10">
                    <h2 className="text-3xl sm:text-5xl font-black tracking-tight leading-tight">
                        Prêt à révolutionner vos rencontres professionnelles ?
                    </h2>
                    <p className="text-indigo-100 text-sm sm:text-base max-w-xl mx-auto leading-relaxed">
                        Commandez votre carte AURACARD aujourd’hui et recevez-la directement à votre bureau ou domicile au Maroc.
                    </p>
                    <div className="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3.5">
                        <a href="#pricing" className="w-full sm:w-auto">
                            <Button
                                variant="secondary"
                                size="lg"
                                className="w-full sm:w-auto min-h-[48px] rounded-xl font-bold text-white px-8 shadow-lg"
                            >
                                <span>Choisir mon forfait</span>
                                <ArrowRight className={`w-4 h-4 ${isRtl ? 'rotate-180 me-2' : 'ms-2'}`} />
                            </Button>
                        </a>
                        <Link href="/pricing" className="w-full sm:w-auto">
                            <Button
                                variant="outline"
                                size="lg"
                                className="w-full sm:w-auto min-h-[48px] rounded-xl font-semibold text-white border-white/40 hover:bg-white/10 px-8"
                            >
                                <span>Consulter tous les forfaits</span>
                            </Button>
                        </Link>
                    </div>
                </div>
            </section>
        </MarketingLayout>
    );
}
