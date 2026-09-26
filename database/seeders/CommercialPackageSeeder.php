<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CardDesign;
use App\Models\CommercialPackage;
use Illuminate\Database\Seeder;

class CommercialPackageSeeder extends Seeder
{
    /**
     * Run the database seeds for Card Designs and Commercial Packages.
     */
    public function run(): void
    {
        // 1. Seed Card Designs Catalog
        $designs = [
            [
                'key' => 'matte_black',
                'name' => [
                    'en' => 'Matte Obsidian Black',
                    'fr' => 'Noir Obsidienne Mat',
                    'ar' => 'أسود أوبسيديان مطفي',
                ],
                'description' => [
                    'en' => 'Sleek ultra-matte finish with stealth laser engraved accents. Premium recycled polymer core.',
                    'fr' => 'Finition ultra-mate élégante avec gravure laser discrète. Cœur en polymère recyclé haut de gamme.',
                    'ar' => 'مظهر أسود مطفي فاخر مع نقوش ليزر دقيقة ومواد بوليمرية معاد تدويرها عالية الجودة.',
                ],
                'material' => 'recycled_pvc',
                'color' => '#111827',
                'preview_image' => '/images/cards/card-matte-black.svg',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'key' => 'metal_gold',
                'name' => [
                    'en' => 'Brushed Royal Gold',
                    'fr' => 'Or Royal Brossé',
                    'ar' => 'ذهبي ملكي مصقول',
                ],
                'description' => [
                    'en' => 'Luxury brushed metallic gold surface with high-durability scratch-resistant coat.',
                    'fr' => 'Surface métallique or brossé de luxe avec revêtement haute résistance aux rayures.',
                    'ar' => 'سطح معدني ذهبي مصقول فاخر ومقاوم للخدوش مع شريحة NFC مدمجة فائقة الاستجابة.',
                ],
                'material' => 'metal_hybrid',
                'color' => '#D97706',
                'preview_image' => '/images/cards/card-metal-gold.svg',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'key' => 'bamboo_wood',
                'name' => [
                    'en' => 'Eco Organic Bamboo',
                    'fr' => 'Bambou Organique Éco',
                    'ar' => 'خشب البامبو العضوي',
                ],
                'description' => [
                    'en' => 'Sustainable natural carved bamboo wood. Each card features unique organic grain.',
                    'fr' => 'Bois de bambou naturel gravé et écologique. Chaque carte possède un veinage unique.',
                    'ar' => 'خشب بامبو طبيعي ومستدام 100%، تتميز كل بطاقة بنقوش خشبية طبيعية فريدة من نوعها.',
                ],
                'material' => 'natural_bamboo',
                'color' => '#78350F',
                'preview_image' => '/images/cards/card-bamboo-wood.svg',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'key' => 'aura_gradient',
                'name' => [
                    'en' => 'Aura Neon Hologram',
                    'fr' => 'Aura Néon Holographique',
                    'ar' => 'أورا هولوغرام النيون',
                ],
                'description' => [
                    'en' => 'Iconic AURACARD indigo-teal chromatic gradient with dynamic holographic reflection.',
                    'fr' => 'Dégradé chromatique indigo-sarcelle emblématique d\'AURACARD avec reflet holographique.',
                    'ar' => 'التدرج اللوني الأيقوني لمنصة أورا كارد بين الأزرق النيلي والتركواز مع انعكاس ضوئي مبهر.',
                ],
                'material' => 'gloss_pvc',
                'color' => '#6C5CE7',
                'preview_image' => '/images/cards/card-aura-gradient.svg',
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($designs as $design) {
            CardDesign::updateOrCreate(
                ['key' => $design['key']],
                $design
            );
        }

        // 2. Seed Commercial Packages (Business Source of Truth)
        $packages = [
            [
                'key' => 'solo',
                'name' => [
                    'en' => 'AURACARD Solo',
                    'fr' => 'AURACARD Solo',
                    'ar' => 'أورا كارد سولو',
                ],
                'description' => [
                    'en' => 'The definitive smart identity solution for independent consultants, founders, and executives.',
                    'fr' => 'La solution d\'identité intelligente idéale pour les indépendants, consultants et dirigeants.',
                    'ar' => 'الحل الأمثل والذكي للمهنيين والمستشارين ورواد الأعمال المستقلين.',
                ],
                'price' => 150.00,
                'currency' => 'MAD',
                'duration_months' => 12,
                'physical_cards_included' => 1,
                'profiles_included' => 1,
                'edit_credits_per_profile' => 3,
                'allows_customer_owned_card' => false,
                'requires_customer_owned_card' => false,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 1,
                'features_list' => [
                    'en' => [
                        '1 Physical Smart NFC Card included',
                        '1 Independent Digital Profile',
                        '3 Profile Modification Credits after first publish',
                        'First publication 100% Free',
                        'Instant vCard 4.0 Contact Download',
                        'Real-time Tap & View Telemetry',
                        '12-Month Service Entitlement',
                    ],
                    'fr' => [
                        '1 Carte NFC physique connectée incluse',
                        '1 Profil digital professionnel indépendant',
                        '3 Crédits de modification après la 1ère publication',
                        'Première publication 100% Gratuite',
                        'Téléchargement instantané de vCard 4.0',
                        'Télémétrie en temps réel des scans et vues',
                        'Abonnement annuel 12 mois inclus',
                    ],
                    'ar' => [
                        'بطاقة NFC ذكية وملموسة واحدة مشمولة',
                        'ملف تعريفي رقمي مستقل واحترافي',
                        '3 أرصدة تعديل بعد النشر الأول للملف',
                        'النشر الأول مجاني بالكامل',
                        'تحميل فوري لبطاقة جهة الاتصال vCard 4.0',
                        'إحصائيات فورية للمسات والزيارات',
                        'صلاحية سنوية لمدة 12 شهراً',
                    ],
                ],
            ],
            [
                'key' => 'trio',
                'name' => [
                    'en' => 'AURACARD Trio',
                    'fr' => 'AURACARD Trio',
                    'ar' => 'أورا كارد ثلاثي',
                ],
                'description' => [
                    'en' => 'Designed for small teams, partners, or multi-role professionals with distinct card designs.',
                    'fr' => 'Conçu pour les petites équipes, associés ou professionnels multi-activités avec choix de design.',
                    'ar' => 'مصمم للفرق الصغيرة والشركاء أو لأصحاب الأنشطة المتعددة مع حرية اختيار تصاميم البطاقات.',
                ],
                'price' => 249.00,
                'currency' => 'MAD',
                'duration_months' => 12,
                'physical_cards_included' => 3,
                'profiles_included' => 3,
                'edit_credits_per_profile' => 5, // 5 credits strictly isolated per profile
                'allows_customer_owned_card' => false,
                'requires_customer_owned_card' => false,
                'is_active' => true,
                'is_featured' => true, // Popular choice
                'sort_order' => 2,
                'features_list' => [
                    'en' => [
                        '3 Physical Smart NFC Cards included',
                        'Choose material & finish per card from catalog',
                        '3 Completely Independent Digital Profiles',
                        '5 Modification Credits strictly per profile',
                        'First publication Free for each profile',
                        'Isolated credit ledger (no credit pooling)',
                        'Full Lead Capture & Inquiry Inbox',
                        '12-Month Service Entitlement',
                    ],
                    'fr' => [
                        '3 Cartes NFC physiques connectées incluses',
                        'Choix du matériau et finition pour chaque carte',
                        '3 Profils digitaux entièrement indépendants',
                        '5 Crédits de modification isolés par profil',
                        'Première publication gratuite pour chaque profil',
                        'Pas de mutualisation des crédits (sécurité totale)',
                        'Boîte de réception des prospects intégrée',
                        'Abonnement annuel 12 mois inclus',
                    ],
                    'ar' => [
                        '3 بطاقات NFC ذكية ملموسة مشمولة',
                        'اختيار المادة واللون لكل بطاقة من الكتالوج',
                        '3 ملفات تعريفية رقمية مستقلة تماماً',
                        '5 أرصدة تعديل مستقلة لكل ملف على حدة',
                        'النشر الأول مجاني لكل ملف تعريف',
                        'فصل تام للأرصدة بين البطاقات لمنع التداخل',
                        'نموذج استقبال الطلبات والرسائل المباشرة',
                        'صلاحية سنوية لمدة 12 شهراً',
                    ],
                ],
            ],
            [
                'key' => 'business_5',
                'name' => [
                    'en' => 'AURACARD Business 5',
                    'fr' => 'AURACARD Business 5',
                    'ar' => 'أورا كارد للأعمال 5',
                ],
                'description' => [
                    'en' => 'Corporate team identity pack. 5 premium NFC cards with custom profile management and lead capture.',
                    'fr' => 'Pack d\'identité pour entreprises. 5 cartes NFC haut de gamme avec gestion d\'équipe et prospects.',
                    'ar' => 'باقة الهوية المؤسسية للشركات. 5 بطاقات ذكية فاخرة مع إدارة متقدمة واستقبال طلبات العملاء.',
                ],
                'price' => 399.00,
                'currency' => 'MAD',
                'duration_months' => 12,
                'physical_cards_included' => 5,
                'profiles_included' => 5,
                'edit_credits_per_profile' => 10, // 10 credits strictly isolated per profile
                'allows_customer_owned_card' => false,
                'requires_customer_owned_card' => false,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 3,
                'features_list' => [
                    'en' => [
                        '5 Physical Smart NFC Cards included',
                        'Select any card finishes from catalog for each team member',
                        '5 Independent Corporate Profiles',
                        '10 Modification Credits per profile entitlement',
                        'First publication Free per profile',
                        'White-label option (remove platform badge)',
                        'Advanced Team Analytics & Click Attribution',
                        '12-Month Service Entitlement',
                    ],
                    'fr' => [
                        '5 Cartes NFC physiques connectées incluses',
                        'Sélectionnez les finitions au choix pour chaque collaborateur',
                        '5 Profils professionnels indépendants',
                        '10 Crédits de modification dédiés par profil',
                        'Première publication gratuite par profil',
                        'Option marque blanche (suppression du badge)',
                        'Analytique avancée et attribution des clics',
                        'Abonnement annuel 12 mois inclus',
                    ],
                    'ar' => [
                        '5 بطاقات NFC ذكية مشمولة لفريق العمل',
                        'اختيار التصميم المناسب لكل عضو من الكتالوج',
                        '5 ملفات تعريفية رقمية مستقلة',
                        '10 أرصدة تعديل مخصصة لكل ملف على حدة',
                        'النشر الأول مجاني لكل ملف',
                        'إمكانية إخفاء علامة المنصة (هوية بيضاء خاصة)',
                        'تحليلات متقدمة لروابط وأداء الفريق',
                        'صلاحية سنوية لمدة 12 شهراً',
                    ],
                ],
            ],
            [
                'key' => 'profile_only',
                'name' => [
                    'en' => 'AURACARD Profile Only',
                    'fr' => 'AURACARD Profil Seul',
                    'ar' => 'أورا كارد الملف الرقمي فقط',
                ],
                'description' => [
                    'en' => 'Bring your own compatible writable NFC card. Receive a full AURACARD secure digital identity.',
                    'fr' => 'Apportez votre propre carte NFC compatible. Obtenez une identité digitale sécurisée complète.',
                    'ar' => 'استخدم بطاقة NFC المتوافقة التي تمتلكها بالفعل، واحصل على هوية رقمية آمنة ومتكاملة من أورا كارد.',
                ],
                'price' => 100.00,
                'currency' => 'MAD',
                'duration_months' => 12,
                'physical_cards_included' => 0,
                'profiles_included' => 1,
                'edit_credits_per_profile' => 4,
                'allows_customer_owned_card' => true,
                'requires_customer_owned_card' => true,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 4,
                'features_list' => [
                    'en' => [
                        '0 Physical Cards (bring your own writable NFC tag)',
                        'Cryptographically secure token link generated',
                        'No trust placed on raw hardware UID',
                        '1 Independent Digital Profile',
                        '4 Profile Modification Credits after first publish',
                        'First publication 100% Free',
                        '12-Month Service Entitlement',
                    ],
                    'fr' => [
                        '0 Carte physique (utilisez votre propre puce NFC inscriptible)',
                        'Lien de jeton sécurisé par cryptographie généré',
                        'Aucune confiance accordée à l\'UID matériel brut',
                        '1 Profil digital professionnel indépendant',
                        '4 Crédits de modification après la 1ère publication',
                        'Première publication 100% Gratuite',
                        'Abonnement annuel 12 mois inclus',
                    ],
                    'ar' => [
                        'بدون بطاقة ملموسة (استخدم شريحة NFC المتوافقة التي تملكها)',
                        'رابط رمز تشفيري آمن غير مرتبط برقم الشريحة المادي',
                        'أمان تام دون الاعتماد على UID الشريحة الخارجية',
                        'ملف تعريفي رقمي واحد مستقل',
                        '4 أرصدة تعديل بعد النشر الأول',
                        'النشر الأول مجاني 100%',
                        'صلاحية سنوية لمدة 12 شهراً',
                    ],
                ],
            ],
        ];

        foreach ($packages as $pkg) {
            CommercialPackage::updateOrCreate(
                ['key' => $pkg['key']],
                $pkg
            );
        }
    }
}
