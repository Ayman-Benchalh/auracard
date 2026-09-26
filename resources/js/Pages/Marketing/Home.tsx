import React, { useState } from 'react';
import { Head, Link } from '@inertiajs/react';
import { MarketingLayout } from '@/Layouts/MarketingLayout';
import { Button } from '@/Components/UI/Button';
import { Card } from '@/Components/UI/Card';
import { Badge } from '@/Components/UI/Badge';
import { AuraCardLogo } from '@/Components/AuraCardLogo';
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
                return {
                    bg: 'bg-gradient-to-tr from-[#78350F] via-[#D97706] to-[#FDE68A]',
                    border: 'border-[#B45309]',
                    glow: 'shadow-[#D97706]/30',
                    chip: 'bg-[#92400E]',
                    text: 'text-slate-900',
                };
            case 'bamboo_wood':
                return {
                    bg: 'bg-gradient-to-tr from-[#381B09] via-[#78350F] to-[#92400E]',
                    border: 'border-[#92400E]',
                    glow: 'shadow-[#78350F]/30',
                    chip: 'bg-[#D97706]',
                    text: 'text-amber-50',
                };
            case 'aura_gradient':
                return {
                    bg: 'bg-gradient-to-tr from-[#4F46E5] via-[#7C3AED] to-[#06B6D4]',
                    border: 'border-[#818CF8]',
                    glow: 'shadow-[#7C3AED]/40',
                    chip: 'bg-[#F59E0B]',
                    text: 'text-white',
                };
            case 'matte_black':
            default:
                return {
                    bg: 'bg-gradient-to-tr from-[#090D16] via-[#111827] to-[#1E293B]',
                    border: 'border-slate-700',
                    glow: 'shadow-black/60',
                    chip: 'bg-[#D97706]',
                    text: 'text-slate-100',
                };
        }
    };

    const activeStyle = getCardStyle(activeDesignKey);

    const professions = [
        {
            title: locale === 'ar' ? 'الاستشاريون والمستقلون' : locale === 'en' ? 'Consultants & Freelancers' : 'Consultants & Indépendants',
            desc: locale === 'ar' ? 'مشاركة الخبرات، عروض الأسعار، وتحديد مواعيد الاستشارة مباشرة.' : locale === 'en' ? 'Share credentials, pitch decks, and book discovery sessions directly.' : 'Partagez vos expertises, présentations et prises de rendez-vous en un geste.',
            icon: Briefcase,
            accent: 'from-indigo-500/20 to-purple-500/10 text-indigo-500',
        },
        {
            title: locale === 'ar' ? 'المحامون والمهن القانونية' : locale === 'en' ? 'Lawyers & Legal Advisors' : 'Avocats & Juristes',
            desc: locale === 'ar' ? 'هوية رقمية رصينة ومطابقة للمعايير الأخلاقية والمهنية.' : locale === 'en' ? 'Dignified digital presence compliant with professional legal standards.' : 'Une présence sobre, confidentielle et conforme aux standards déontologiques.',
            icon: Award,
            accent: 'from-amber-500/20 to-yellow-500/10 text-amber-500',
        },
        {
            title: locale === 'ar' ? 'المهندسون والمعماريون' : locale === 'en' ? 'Architects & Engineers' : 'Architectes & Ingénieurs',
            desc: locale === 'ar' ? 'استعراض المشاريع السابقة، المخططات، والكتالوجات بجودة عالية.' : locale === 'en' ? 'Showcase high-res project portfolios and technical documentation.' : 'Présentez vos réalisations architecturales et portfolios en haute définition.',
            icon: Layers,
            accent: 'from-cyan-500/20 to-blue-500/10 text-cyan-500',
        },
        {
            title: locale === 'ar' ? 'الوكلاء العقاريون' : locale === 'en' ? 'Real Estate Brokers' : 'Agents Immobiliers',
            desc: locale === 'ar' ? 'روابط العروض الحصرية، أرقام الواتساب المباشرة، وحفظ فوري لجهة الاتصال.' : locale === 'en' ? 'Direct links to active property listings, WhatsApp, and instant vCard save.' : 'Accès instantané aux mandats exclusifs, chat WhatsApp et contact vCard.',
            icon: Building2,
            accent: 'from-emerald-500/20 to-teal-500/10 text-emerald-500',
        },
        {
            title: locale === 'ar' ? 'المدراء والتنفيذيون' : locale === 'en' ? 'Executives & Founders' : 'Dirigeants & Fondateurs',
            desc: locale === 'ar' ? 'بطاقة ميتاليك فاخرة تعكس مكانة الشركة وتترك انطباعاً استثنائياً.' : locale === 'en' ? 'Executive metallic finish leaving an unforgettable impression.' : 'Une carte métal haut de gamme qui reflète l’envergure de votre entreprise.',
            icon: Users,
            accent: 'from-rose-500/20 to-pink-500/10 text-rose-500',
        },
        {
            title: locale === 'ar' ? 'الأطباء والمهن الصحية' : locale === 'en' ? 'Doctors & Specialists' : 'Médecins & Spécialistes',
            desc: locale === 'ar' ? 'مواعيد العيادة، أرقام الطوارئ، والموقع الجغرافي للعيادة بدقة.' : locale === 'en' ? 'Clinic booking, practice locations, and emergency contacts.' : 'Accès au cabinet médical, prise de rendez-vous et contact d’urgence.',
            icon: ShieldCheck,
            accent: 'from-blue-500/20 to-sky-500/10 text-blue-500',
        },
    ];

    const faqs = [
        {
            q: locale === 'ar' ? 'هل يحتاج الطرف الآخر إلى تطبيق خاص لقراءة بطاقتي؟' : locale === 'en' ? 'Does the other person need an app to read my card?' : 'La personne qui reçoit ma carte a-t-elle besoin d\'une application ?',
            a: locale === 'ar' ? 'إطلاقاً! لا يتطلب الأمر أي تطبيق. يكفي تمرير بطاقتك الذكية على أي هاتف ذكي (آيفون أو أندرويد) ليفتح الملف التعريفي الخاص بك فوراً في المتصفح، أو مسح رمز QR البديل.' : locale === 'en' ? 'No! No application is required. Just tap your smart card against any smartphone (iPhone or Android) and your profile opens instantly in their browser.' : 'Absolument pas ! Aucune application n\'est requise. Un simple contact (tap) NFC avec un smartphone récent (iPhone ou Android) ouvre instantanément votre profil dans le navigateur. Un QR code sécurisé est également présent.',
        },
        {
            q: locale === 'ar' ? 'ما هي الهواتف المتوافقة مع تقنية NFC؟' : locale === 'en' ? 'Which smartphones are compatible with NFC?' : 'Quels smartphones sont compatibles ?',
            a: locale === 'ar' ? 'جميع هواتف iPhone منذ موديل iPhone XR (2018) وحتى أحدث الموديلات، وأكثر من 95% من هواتف Android الحديثة تدعم تقنية NFC مسبقاً وبشكل تلقائي دون أي إعداد.' : locale === 'en' ? 'All iPhones from iPhone XR (2018) onward and over 95% of modern Android devices feature native NFC reading enabled by default.' : 'Pratiquement tous les smartphones modernes ! Tous les iPhone depuis l\'iPhone XR (2018) et plus de 95% des smartphones Android récents intègrent le NFC actif en continu.',
        },
        {
            q: locale === 'ar' ? 'كيف تعمل باقة "الملف الرقمي فقط" (100 درهم)؟' : locale === 'en' ? 'How does the Profile Only package (100 MAD) work?' : 'Comment fonctionne le forfait Profil Seul (100 MAD) ?',
            a: locale === 'ar' ? 'إذا كنت تمتلك بالفعل بطاقة NFC متوافقة أو ملصقاً ذكياً، تمنحك هذه الباقة اشتراكاً كاملاً لمدة 12 شهراً ورابط رمز تشفيري خاص يمكنك برمجته على بطاقتك مع 4 أرصدة تعديل.' : locale === 'en' ? 'If you already own a compatible writable NFC card or tag, this package grants a full 12-month entitlement and a cryptographically secure token link to program onto your tag, with 4 modification credits.' : 'Si vous possédez déjà une carte ou badge NFC inscriptible, cette offre vous donne accès à la plateforme pendant 12 mois avec 4 crédits de modification et un lien token chiffré prêt à être encodé.',
        },
        {
            q: locale === 'ar' ? 'ماذا يحدث عند استهلاك جميع أرصدة التعديل المخصصة للملف؟' : locale === 'en' ? 'What happens when profile modification credits run out?' : 'Que se passe-t-il lorsque mes crédits de modification sont épuisés ?',
            a: locale === 'ar' ? 'يبقى ملفك منشوراً ومتاحاً للجميع طوال فترة اشتراكك السنوي دون انقطاع. يمكنك دائماً تعديل مسودة ملفك وحفظها، وتعبئة رصيد تعديلات إضافي فقط عند الرغبة في نشر التحديثات.' : locale === 'en' ? 'Your profile remains online and fully operational for the entire 12-month period. You can edit drafts anytime and top up additional modification credits when you wish to publish new revisions.' : 'Votre profil reste en ligne et fonctionnel sans interruption. Vous pouvez continuer à modifier votre brouillon à tout moment, et acquérir des recharges de crédits uniquement lorsque vous souhaitez publier une nouvelle version.',
        },
        {
            q: locale === 'ar' ? 'ما هي طرق الدفع المتاحة داخل المغرب؟' : locale === 'en' ? 'What payment methods are supported in Morocco?' : 'Quels sont les modes de paiement acceptés au Maroc ?',
            a: locale === 'ar' ? 'ندعم التحويل البنكي الوطني المباشر، أو الإيداع النقدي عبر شبكات وكالات الدفع المعتمدة في كافة المدن المغربية، مع تفعيل فوري للطلب فور مراجعة الإشعار.' : locale === 'en' ? 'We support standard national bank wire transfers and cash agency branch deposits across all Moroccan cities, with expedited activation upon slip upload.' : 'Nous acceptons les virements bancaires nationaux et les dépôts en espèces via les réseaux d\'agences partenaires partout au Maroc, avec validation rapide dès réception du justificatif.',
        },
    ];

    return (
        <MarketingLayout>
            <Head title="AURACARD - Cartes de Visite Connectées NFC & Profils Professionnels au Maroc" />

            {/* SECTION 1: HERO SECTION */}
            <section className="relative overflow-hidden pt-10 pb-20 lg:pt-16 lg:pb-32">
                {/* Background Ambient Lighting Effect */}
                <div className="absolute top-1/4 start-1/2 -translate-x-1/2 w-[700px] h-[500px] bg-gradient-to-tr from-[#6C5CE7]/20 via-[#38BDF8]/15 to-transparent blur-[120px] -z-10 rounded-full pointer-events-none" />

                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                        {/* Hero Text Content */}
                        <div className="lg:col-span-7 space-y-6 text-center lg:text-start">
                            {/* Moroccan Tech Innovation Badge */}
                            <div className="inline-flex items-center gap-2.5 px-4 py-2 rounded-full bg-slate-900/5 dark:bg-white/10 text-slate-800 dark:text-slate-200 text-xs font-bold border border-slate-200 dark:border-slate-800 shadow-sm">
                                <span className="flex h-2 w-2 rounded-full bg-[#10B981] animate-pulse" />
                                <span>Plateforme Innovante • Conçue pour le Maroc</span>
                                <span className="text-slate-400">|</span>
                                <span className="text-[#6C5CE7] dark:text-[#818CF8]">v2.0 Commerciale</span>
                            </div>

                            {/* Main Headline */}
                            <h1 className="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight text-slate-900 dark:text-white leading-[1.12]">
                                {locale === 'ar' ? (
                                    <>
                                        بطاقة الأعمال الذكية <br />
                                        <span className="bg-gradient-to-r from-[#6C5CE7] via-[#818CF8] to-[#06B6D4] bg-clip-text text-transparent">
                                            التي تفتح كل الأبواب
                                        </span>
                                    </>
                                ) : (
                                    <>
                                        La Carte de Visite Connectée <br />
                                        <span className="bg-gradient-to-r from-[#6C5CE7] via-[#818CF8] to-[#06B6D4] bg-clip-text text-transparent">
                                            qui marque les esprits.
                                        </span>
                                    </>
                                )}
                            </h1>

                            {/* Subtitle */}
                            <p className="text-base sm:text-lg text-slate-600 dark:text-slate-300 max-w-xl mx-auto lg:mx-0 font-normal leading-relaxed">
                                {locale === 'ar'
                                    ? 'شارك هويتك المهنية، بيانات الاتصال، وروابط أعمالك بلمسة NFC واحدة أو مسح QR. بدون تطبيق، بدون ورق، مع حماية تامة لبياناتك.'
                                    : 'Partagez instantanément votre profil professionnel, vos coordonnées et votre catalogue d’un simple geste NFC. Zéro papier, zéro application, sécurisé et conçu pour durer.'}
                            </p>

                            {/* Action Buttons */}
                            <div className="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 pt-2">
                                <a href="#pricing">
                                    <Button variant="primary" size="lg" className="w-full sm:w-auto rounded-2xl shadow-xl shadow-[#6C5CE7]/25 font-bold px-7">
                                        <span>Commander ma carte</span>
                                        <ArrowRight className={`w-5 h-5 ${isRtl ? 'rotate-180 me-2' : 'ms-2'}`} />
                                    </Button>
                                </a>

                                <a href="#demo-profile">
                                    <Button variant="outline" size="lg" className="w-full sm:w-auto rounded-2xl font-semibold px-6 border-slate-300 dark:border-slate-700">
                                        <Eye className="w-4 h-4 me-2 text-[#6C5CE7]" />
                                        <span>Voir démo en direct</span>
                                    </Button>
                                </a>
                            </div>

                            {/* Trust Signals */}
                            <div className="pt-4 flex flex-wrap items-center justify-center lg:justify-start gap-6 text-xs text-slate-500 font-medium">
                                <div className="flex items-center gap-2">
                                    <CheckCircle2 className="w-4 h-4 text-emerald-500" />
                                    <span>Livraison express partout au Maroc</span>
                                </div>
                                <div className="flex items-center gap-2">
                                    <CheckCircle2 className="w-4 h-4 text-emerald-500" />
                                    <span>Paiement par virement ou espèces</span>
                                </div>
                                <div className="flex items-center gap-2">
                                    <CheckCircle2 className="w-4 h-4 text-emerald-500" />
                                    <span>Garantie puce NFC 12 mois</span>
                                </div>
                            </div>
                        </div>

                        {/* Interactive Hero Visual: 3D NFC Card Showcase */}
                        <div className="lg:col-span-5 flex flex-col items-center justify-center relative">
                            {/* Concentric Aura Rings */}
                            <div className="absolute inset-0 flex items-center justify-center pointer-events-none">
                                <div className="w-72 h-72 rounded-full border border-[#6C5CE7]/30 animate-ping opacity-25" />
                                <div className="w-88 h-88 rounded-full border border-[#38BDF8]/20" />
                            </div>

                            {/* The 3D NFC Smart Card Preview */}
                            <div
                                className={`w-full max-w-[370px] h-[230px] rounded-3xl p-6 relative flex flex-col justify-between border-2 shadow-2xl transition-all duration-500 group select-none transform hover:-rotate-1 hover:scale-[1.02] ${activeStyle.bg} ${activeStyle.border} ${activeStyle.glow}`}
                            >
                                {/* Metallic/Gloss Glare Overlay */}
                                <div className="absolute inset-0 rounded-3xl bg-gradient-to-tr from-white/10 via-transparent to-transparent pointer-events-none" />

                                {/* Card Header: Smart Chip & Wireless Wave */}
                                <div className="flex items-center justify-between z-10">
                                    <div className="flex items-center gap-3">
                                        {/* Physical Chip Graphic */}
                                        <div className="w-12 h-9 rounded-lg bg-gradient-to-tr from-amber-400 to-amber-600 border border-amber-300/80 relative shadow-xs overflow-hidden flex items-center justify-center">
                                            <div className="w-full h-px bg-amber-800/40 absolute top-1/2" />
                                            <div className="h-full w-px bg-amber-800/40 absolute left-1/2" />
                                            <div className="w-4 h-4 rounded-full border border-amber-800/40" />
                                        </div>

                                        {/* Contactless Waves */}
                                        <div className="flex items-center text-white/80">
                                            <svg className="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5">
                                                <path d="M8.5 16.5a5 5 0 0 1 0-9" />
                                                <path d="M12 19a8.5 8.5 0 0 1 0-14" />
                                                <path d="M15.5 21.5a12 12 0 0 1 0-19" />
                                            </svg>
                                        </div>
                                    </div>

                                    {/* Tap Notification Badge */}
                                    <div className="px-2.5 py-1 rounded-full bg-black/40 backdrop-blur-md text-[10px] text-white font-mono tracking-widest border border-white/15">
                                        NFC ENABLED
                                    </div>
                                </div>

                                {/* Card Center: Holographic Symbol */}
                                <div className="flex items-center justify-center py-2 z-10">
                                    <div className="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center shadow-lg">
                                        <Sparkles className="w-6 h-6 text-white animate-pulse" />
                                    </div>
                                </div>

                                {/* Card Footer: Brand & Holder */}
                                <div className="flex items-end justify-between z-10">
                                    <div>
                                        <div className="text-[10px] tracking-widest text-slate-300 font-bold uppercase">
                                            SMART IDENTITY
                                        </div>
                                        <div className="text-sm font-extrabold text-white tracking-wider">
                                            YASSINE BENNANI
                                        </div>
                                    </div>

                                    <div className="text-end">
                                        <div className="text-base font-black tracking-widest text-white leading-none">
                                            AURA<span className="text-[#38BDF8]">CARD</span>
                                        </div>
                                        <div className="text-[9px] text-slate-300 tracking-wider">
                                            CASABLANCA • MAROC
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Live Finish Switcher below the Card */}
                            <div className="mt-6 flex items-center gap-2 p-1.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-md">
                                {cardDesigns.map((d) => (
                                    <button
                                        key={d.key}
                                        type="button"
                                        onClick={() => setActiveDesignKey(d.key)}
                                        className={`px-3 py-1.5 rounded-xl text-xs font-bold transition-all ${
                                            activeDesignKey === d.key
                                                ? 'bg-[#6C5CE7] text-white shadow-xs'
                                                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
                                        }`}
                                    >
                                        {getLocalized(d.name, d.key)}
                                    </button>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* SECTION 2: NFC PRODUCT VISUALS & FINISHES CATALOG */}
            <section id="products" className="py-20 bg-slate-50 dark:bg-[#0C1222] border-y border-slate-200/80 dark:border-slate-800/80 transition-colors">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                    <div className="text-center max-w-3xl mx-auto space-y-3">
                        <Badge variant="primary" className="px-3 py-1 text-xs uppercase font-bold tracking-wider">
                            Collection Finitions 2026
                        </Badge>
                        <h2 className="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight">
                            Des matériaux d’exception conçus pour durer
                        </h2>
                        <p className="text-slate-600 dark:text-slate-400 text-sm sm:text-base">
                            Chaque carte AURACARD intègre une puce NFC haute sensibilité NTAG215 avec antenne optimisée et protection contre les interférences magnétiques.
                        </p>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        {cardDesigns.map((design) => {
                            const style = getCardStyle(design.key);
                            return (
                                <Card
                                    key={design.id}
                                    className="p-6 rounded-3xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#11182A] space-y-5 hover:shadow-xl transition-all duration-300 group"
                                >
                                    {/* Card Mini Preview Box */}
                                    <div
                                        className={`h-40 rounded-2xl p-4 flex flex-col justify-between border relative overflow-hidden transition-transform duration-300 group-hover:scale-[1.02] ${style.bg} ${style.border} ${style.glow}`}
                                    >
                                        <div className="flex justify-between items-center z-10">
                                            <div className="w-7 h-5 rounded bg-amber-400/80 border border-amber-300" />
                                            <span className="text-[9px] font-mono text-white/80 uppercase">NTAG215</span>
                                        </div>
                                        <div className="z-10">
                                            <span className="text-[10px] font-bold text-white/90 uppercase tracking-widest">
                                                AURA CARD
                                            </span>
                                        </div>
                                    </div>

                                    {/* Description */}
                                    <div className="space-y-2">
                                        <h3 className="font-extrabold text-base text-slate-900 dark:text-white">
                                            {getLocalized(design.name)}
                                        </h3>
                                        <p className="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                            {getLocalized(design.description)}
                                        </p>
                                    </div>

                                    <div className="pt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-xs font-semibold">
                                        <span className="text-slate-400">Matériau :</span>
                                        <span className="capitalize text-slate-800 dark:text-slate-200">
                                            {design.material.replace('_', ' ')}
                                        </span>
                                    </div>
                                </Card>
                            );
                        })}
                    </div>
                </div>
            </section>

            {/* SECTION 3: HOW IT WORKS */}
            <section id="how-it-works" className="py-20 bg-white dark:bg-[#080D1A] transition-colors">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">
                    <div className="text-center max-w-3xl mx-auto space-y-3">
                        <Badge variant="neutral" className="px-3 py-1 text-xs uppercase font-bold tracking-wider">
                            Simplicité Absolue
                        </Badge>
                        <h2 className="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight">
                            Comment fonctionne AURA CARD ?
                        </h2>
                        <p className="text-slate-600 dark:text-slate-400 text-sm sm:text-base">
                            Passez au networking moderne en 4 étapes simples et fluides.
                        </p>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-4 gap-8 relative">
                        {[
                            {
                                step: '01',
                                title: 'Commandez votre Pack',
                                desc: 'Choisissez votre forfait (Solo, Trio, Business 5 ou Profil Seul) et vos designs favoris.',
                                icon: CreditCard,
                            },
                            {
                                step: '02',
                                title: 'Créez votre Profil',
                                desc: 'Personnalisez votre page en 2 minutes : photo, bio, liens, vCard et formulaire de contact.',
                                icon: Sliders,
                            },
                            {
                                step: '03',
                                title: 'Touchez n’importe quel smartphone',
                                desc: 'Approchez votre carte du téléphone de votre interlocuteur. Zéro application requise.',
                                icon: Smartphone,
                            },
                            {
                                step: '04',
                                title: 'Contact Enregistré',
                                desc: 'Votre prospect enregistre directement votre fiche vCard 4.0 complète dans ses contacts.',
                                icon: Download,
                            },
                        ].map((item, idx) => {
                            const Icon = item.icon;
                            return (
                                <div
                                    key={item.step}
                                    className="p-6 rounded-3xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-[#0E1526] space-y-4 relative group hover:-translate-y-1 transition-all duration-200"
                                >
                                    <div className="flex items-center justify-between">
                                        <div className="w-12 h-12 rounded-2xl bg-[#6C5CE7]/15 text-[#6C5CE7] flex items-center justify-center font-bold">
                                            <Icon className="w-6 h-6" />
                                        </div>
                                        <span className="text-2xl font-black text-slate-300 dark:text-slate-700">
                                            {item.step}
                                        </span>
                                    </div>
                                    <h3 className="text-base font-bold text-slate-900 dark:text-white">
                                        {item.title}
                                    </h3>
                                    <p className="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                        {item.desc}
                                    </p>
                                </div>
                            );
                        })}
                    </div>
                </div>
            </section>

            {/* SECTION 4: DEMO PROFILE PREVIEW (Live Interactive Phone Mockup) */}
            <section id="demo-profile" className="py-20 bg-slate-900 text-white relative overflow-hidden">
                {/* Background Glow */}
                <div className="absolute top-1/2 start-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-[#6C5CE7]/20 blur-[140px] pointer-events-none rounded-full" />

                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12 relative z-10">
                    <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                        <div className="lg:col-span-6 space-y-6">
                            <Badge variant="primary" className="px-3 py-1 text-xs uppercase font-bold tracking-wider">
                                Expérience Visiteur Fluide
                            </Badge>
                            <h2 className="text-3xl sm:text-5xl font-black tracking-tight leading-tight">
                                Votre profil digital interactif sans téléchargement
                            </h2>
                            <p className="text-slate-300 text-sm sm:text-base leading-relaxed">
                                Dès que votre carte est scannée, une page web responsive ultra-rapide s’affiche. Vos interlocuteurs peuvent immédiatement télécharger votre fiche contact, vous appeler ou vous envoyer un message WhatsApp sans friction.
                            </p>

                            <div className="space-y-3.5 pt-2">
                                {[
                                    'Téléchargement instantané de vCard 4.0 certifiée',
                                    '12 blocs de contenu modulaires (Bio, Liens, Catalogue, RDV)',
                                    'Capture directe de prospects dans votre tableau de bord',
                                    'Statistiques de consultation en temps réel anonymisées',
                                ].map((feature) => (
                                    <div key={feature} className="flex items-center gap-3 text-sm text-slate-200">
                                        <div className="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                                            <Check className="w-3.5 h-3.5" />
                                        </div>
                                        <span>{feature}</span>
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Interactive Phone Mockup */}
                        <div className="lg:col-span-6 flex justify-center">
                            <div className="w-full max-w-[320px] rounded-[44px] bg-[#0A0E1A] border-[6px] border-slate-700/80 p-4 shadow-2xl shadow-black/80 space-y-4 relative">
                                {/* Phone Speaker & Camera Notch */}
                                <div className="w-24 h-4 rounded-full bg-slate-800 mx-auto" />

                                {/* Profile Demo Card Interior */}
                                <div className="rounded-3xl bg-[#141A2E] p-4 text-center space-y-3 border border-slate-700/60">
                                    {/* Avatar */}
                                    <div className="w-16 h-16 rounded-full bg-gradient-to-tr from-[#6C5CE7] to-[#38BDF8] mx-auto flex items-center justify-center font-black text-xl text-white shadow-md">
                                        YB
                                    </div>
                                    <div>
                                        <h4 className="font-extrabold text-base text-white">Yassine Bennani</h4>
                                        <p className="text-xs text-[#818CF8] font-medium">Consultant en Transformation Digitale</p>
                                        <p className="text-[11px] text-slate-400">Casablanca, Maroc</p>
                                    </div>

                                    {/* Action Buttons */}
                                    <div className="grid grid-cols-2 gap-2 pt-2">
                                        <div className="p-2.5 rounded-xl bg-[#6C5CE7] text-white text-xs font-bold flex items-center justify-center gap-1.5 shadow-sm">
                                            <Download className="w-3.5 h-3.5" />
                                            <span>vCard</span>
                                        </div>
                                        <div className="p-2.5 rounded-xl bg-emerald-600 text-white text-xs font-bold flex items-center justify-center gap-1.5 shadow-sm">
                                            <PhoneCall className="w-3.5 h-3.5" />
                                            <span>WhatsApp</span>
                                        </div>
                                    </div>
                                </div>

                                {/* Quick Links Preview */}
                                <div className="space-y-2">
                                    <div className="p-2.5 rounded-xl bg-[#111827] border border-slate-800 flex items-center justify-between text-xs">
                                        <span className="text-slate-300 font-medium">Portfolio & Projets</span>
                                        <ArrowRight className="w-3.5 h-3.5 text-slate-500" />
                                    </div>
                                    <div className="p-2.5 rounded-xl bg-[#111827] border border-slate-800 flex items-center justify-between text-xs">
                                        <span className="text-slate-300 font-medium">Prendre Rendez-vous (Calendly)</span>
                                        <ArrowRight className="w-3.5 h-3.5 text-slate-500" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* SECTION 5: PROFESSIONS & USE CASES */}
            <section id="professions" className="py-20 bg-white dark:bg-[#080D1A] transition-colors">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                    <div className="text-center max-w-3xl mx-auto space-y-3">
                        <Badge variant="neutral" className="px-3 py-1 text-xs uppercase font-bold tracking-wider">
                            Cas d’usage métier
                        </Badge>
                        <h2 className="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight">
                            Adapté à chaque secteur professionnel
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
                                    className="p-6 rounded-3xl border border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-[#0E1526]/60 hover:bg-white dark:hover:bg-[#11182A] hover:shadow-lg transition-all space-y-4"
                                >
                                    <div className={`w-12 h-12 rounded-2xl bg-gradient-to-tr ${prof.accent} flex items-center justify-center font-bold`}>
                                        <Icon className="w-6 h-6" />
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

            {/* SECTION 6: DYNAMIC DATABASE PACKAGES PRICING */}
            <section id="pricing" className="py-20 bg-slate-50 dark:bg-[#0A0F1E] border-y border-slate-200/80 dark:border-slate-800/80 transition-colors">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                    <div className="text-center max-w-3xl mx-auto space-y-3">
                        <Badge variant="primary" className="px-3 py-1 text-xs uppercase font-bold tracking-wider">
                            Tarification Transparente
                        </Badge>
                        <h2 className="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight">
                            Des forfaits complets pour 12 mois
                        </h2>
                        <p className="text-slate-600 dark:text-slate-400 text-sm sm:text-base">
                            Tous nos packs incluent l’hébergement de votre profil, la première publication gratuite, et la conformité CNDP.
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
                                            ? 'bg-white dark:bg-[#141A2E] border-2 border-[#6C5CE7] shadow-xl shadow-[#6C5CE7]/15 ring-2 ring-[#6C5CE7]/30 scale-[1.02]'
                                            : 'bg-white dark:bg-[#111728] border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md'
                                    }`}
                                >
                                    {/* Dynamic Database Featured Badge */}
                                    {isFeatured && (
                                        <div className="absolute -top-3.5 start-1/2 -translate-x-1/2">
                                            <span className="px-3.5 py-1 rounded-full bg-gradient-to-r from-[#6C5CE7] to-[#38BDF8] text-white text-[11px] font-black uppercase tracking-wider shadow-md">
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

                                        {/* Price - STRICTLY FROM DATABASE */}
                                        <div className="pt-2 border-t border-slate-100 dark:border-slate-800/80">
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

                                        {/* Core Quotas Badges */}
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
                                                <span>Première publication 100% Gratuite</span>
                                            </li>
                                            <li className="flex items-start gap-2">
                                                <Check className="w-3.5 h-3.5 text-emerald-500 mt-0.5 shrink-0" />
                                                <span>Crédits isolés par profil (sécurité totale)</span>
                                            </li>
                                            <li className="flex items-start gap-2">
                                                <Check className="w-3.5 h-3.5 text-emerald-500 mt-0.5 shrink-0" />
                                                <span>Téléchargement vCard 4.0 sans application</span>
                                            </li>
                                            {pkg.physical_cards_included === 0 && (
                                                <li className="flex items-start gap-2 text-indigo-500 font-semibold">
                                                    <Check className="w-3.5 h-3.5 mt-0.5 shrink-0" />
                                                    <span>Utilisez votre propre puce NFC compatible</span>
                                                </li>
                                            )}
                                        </ul>
                                    </div>

                                    {/* Direct Checkout CTA Button */}
                                    <div className="pt-6">
                                        <Link href={`/checkout/package/${pkg.id}`} className="block">
                                            <Button
                                                variant={isFeatured ? 'primary' : 'outline'}
                                                className={`w-full justify-center rounded-2xl font-bold text-xs py-3 ${
                                                    isFeatured ? 'shadow-md shadow-[#6C5CE7]/25' : ''
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

            {/* SECTION 7: SECURITY & PRIVACY */}
            <section className="py-20 bg-white dark:bg-[#080D1A] transition-colors">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                    <div className="p-8 sm:p-12 rounded-3xl bg-slate-900 text-white relative overflow-hidden border border-slate-800">
                        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                            <div className="lg:col-span-8 space-y-4">
                                <Badge variant="primary" className="px-3 py-1 text-xs uppercase font-bold tracking-wider">
                                    Sécurité & Souveraineté
                                </Badge>
                                <h3 className="text-2xl sm:text-3xl font-black tracking-tight">
                                    Architecture anti-fraude et conformité CNDP
                                </h3>
                                <p className="text-slate-300 text-sm leading-relaxed">
                                    Contrairement aux solutions basiques qui s’appuient sur l’UID matériel non sécurisé des cartes, AURA CARD génère des jetons cryptographiques infalsifiables. Toutes les statistiques sont anonymisées avec salage HMAC et aucune donnée de géolocalisation invasive n’est enregistrée.
                                </p>
                            </div>
                            <div className="lg:col-span-4 flex flex-col gap-3">
                                <div className="p-4 rounded-2xl bg-white/5 border border-white/10 flex items-center gap-3">
                                    <Lock className="w-5 h-5 text-[#818CF8]" />
                                    <span className="text-xs font-semibold">Chiffrement AES-256 & SHA-256</span>
                                </div>
                                <div className="p-4 rounded-2xl bg-white/5 border border-white/10 flex items-center gap-3">
                                    <Shield className="w-5 h-5 text-emerald-400" />
                                    <span className="text-xs font-semibold">Zéro dépendance UID matériel</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* SECTION 8: FAQ ACCORDION */}
            <section id="faq" className="py-20 bg-slate-50 dark:bg-[#0C1222] border-t border-slate-200/80 dark:border-slate-800/80 transition-colors">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
                    <div className="text-center space-y-3">
                        <Badge variant="neutral" className="px-3 py-1 text-xs uppercase font-bold tracking-wider">
                            Questions Fréquentes
                        </Badge>
                        <h2 className="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white tracking-tight">
                            Tout ce que vous devez savoir
                        </h2>
                    </div>

                    <div className="space-y-4">
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
                                        className="w-full p-5 text-start flex items-center justify-between gap-4 font-bold text-slate-900 dark:text-white text-sm sm:text-base hover:text-[#6C5CE7] transition-colors"
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

            {/* SECTION 9: FINAL CALL TO ACTION */}
            <section className="py-20 bg-gradient-to-tr from-[#6C5CE7] via-[#5B4BC4] to-[#4F46E5] text-white text-center relative overflow-hidden">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 relative z-10">
                    <h2 className="text-3xl sm:text-5xl font-black tracking-tight leading-tight">
                        Prêt à révolutionner vos rencontres professionnelles ?
                    </h2>
                    <p className="text-indigo-100 text-sm sm:text-base max-w-xl mx-auto">
                        Commandez votre carte AURACARD aujourd’hui et recevez-la directement à votre bureau ou domicile au Maroc.
                    </p>
                    <div className="pt-2 flex flex-col sm:flex-row items-center justify-center gap-4">
                        <a href="#pricing">
                            <Button variant="secondary" size="lg" className="rounded-2xl font-black text-slate-900 px-8 shadow-xl">
                                <span>Choisir mon forfait</span>
                                <ArrowRight className={`w-4 h-4 ${isRtl ? 'rotate-180 me-2' : 'ms-2'}`} />
                            </Button>
                        </a>
                        <Link href="/pricing">
                            <Button variant="outline" size="lg" className="rounded-2xl font-bold text-white border-white/40 hover:bg-white/10 px-8">
                                <span>Consulter tous les tarifs</span>
                            </Button>
                        </Link>
                    </div>
                </div>
            </section>
        </MarketingLayout>
    );
}
