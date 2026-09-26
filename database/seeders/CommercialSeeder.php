<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\PaymentSetting;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class CommercialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Features Catalog
        $featuresData = [
            [
                'key' => 'profile.unlimited_edits',
                'name' => ['en' => 'Unlimited Edits & Publications', 'fr' => 'Modifications et Publications Illimitées', 'ar' => 'تعديل ونشر غير محدود'],
                'description' => ['en' => 'Publish changes at zero credit cost anytime.', 'fr' => 'Publiez sans consommer de crédits.', 'ar' => 'تعديل الملف ونشره مجاناً دون استهلاك أرصدة.'],
            ],
            [
                'key' => 'profile.premium_templates',
                'name' => ['en' => 'Premium Presentation Templates', 'fr' => 'Modèles de Présentation Premium', 'ar' => 'قوالب عرض متميزة'],
                'description' => ['en' => 'Access executive and luxury presentation layouts.', 'fr' => 'Accédez aux modèles haut de gamme.', 'ar' => 'إمكانية استخدام قوالب رجال الأعمال والتصاميم الفاخرة.'],
            ],
            [
                'key' => 'profile.premium_themes',
                'name' => ['en' => 'Tailored Executive Themes', 'fr' => 'Thèmes Exécutifs Sur Mesure', 'ar' => 'سمات وألوان احترافية'],
                'description' => ['en' => 'Dark obsidian, midnight sapphire, and custom typography.', 'fr' => 'Palettes sombres raffinées et polices sur mesure.', 'ar' => 'ألوان وتدرجات داكنة راقية مع خطوط مخصصة.'],
            ],
            [
                'key' => 'profile.advanced_blocks',
                'name' => ['en' => 'Advanced Modular Blocks', 'fr' => 'Blocs Modulaires Avancés', 'ar' => 'وحدات تفاعلية متقدمة'],
                'description' => ['en' => 'Appointment booking, video embeds, product showcases, and FAQ accordions.', 'fr' => 'Prise de rendez-vous, vidéos et catalogue de produits.', 'ar' => 'حجز المواعيد، تضمين الفيديو، وعرض المنتجات.'],
            ],
            [
                'key' => 'analytics.basic',
                'name' => ['en' => 'Real-Time Tap & View Telemetry', 'fr' => 'Télémétrie des Vues et Scans NFC', 'ar' => 'إحصائيات المسح والنقر الفورية'],
                'description' => ['en' => 'Track profile views, NFC card taps, and vCard downloads.', 'fr' => 'Suivez les visites et les téléchargements vCard.', 'ar' => 'تتبع عدد الزيارات ولمسات البطاقة وتحميل جهة الاتصال.'],
            ],
            [
                'key' => 'analytics.advanced',
                'name' => ['en' => 'Advanced Click & Device Attribution', 'fr' => 'Analytique Avancée & Appareils', 'ar' => 'تحليلات تفصيلية للروابط والأجهزة'],
                'description' => ['en' => 'Breakdown of social clicks, contact channels, and platforms.', 'fr' => 'Analyse détaillée des canaux et clics.', 'ar' => 'تحليل الروابط الأكثر نقراً ومصادر الزيارات.'],
            ],
            [
                'key' => 'leads.enabled',
                'name' => ['en' => 'Inquiry & Lead Capture Form', 'fr' => 'Formulaire de Capture de Prospects', 'ar' => 'نموذج استقبال الطلبات والرسائل'],
                'description' => ['en' => 'Receive direct business leads and inquiries directly into your dashboard.', 'fr' => 'Recevez des prospects qualifiés directement dans votre tableau de bord.', 'ar' => 'استقبال رسائل وطلبات العملاء مباشرة في لوحة التحكم.'],
            ],
            [
                'key' => 'documents.enabled',
                'name' => ['en' => 'Document & Portfolio Downloads', 'fr' => 'Téléchargement de Documents et Decks', 'ar' => 'إرفاق المستندات والعروض التقديمية'],
                'description' => ['en' => 'Attach corporate brochures, resumes, and PDF presentations.', 'fr' => 'Attachez vos brochures et catalogues PDF.', 'ar' => 'إرفاق ملفات PDF والعروض التقديمية للتحميل.'],
            ],
            [
                'key' => 'branding.remove_aura',
                'name' => ['en' => 'Remove AURA CARD Branding', 'fr' => 'Supprimer la Marque AURA CARD', 'ar' => 'إزالة شعار المنصة'],
                'description' => ['en' => 'White-label experience without footer branding.', 'fr' => 'Expérience en marque blanche complète.', 'ar' => 'إزالة علامة AURA CARD للحصول على هوية مستقلة تماماً.'],
            ],
            [
                'key' => 'profile.extra_profiles',
                'name' => ['en' => 'Multi-Profile Slots', 'fr' => 'Profils Multiples', 'ar' => 'إدارة ملفات تعريف متعددة'],
                'description' => ['en' => 'Manage personal, corporate, or secondary digital profiles.', 'fr' => 'Gérez plusieurs profils numériques.', 'ar' => 'إنشاء وإدارة أكثر من ملف تعريف في حساب واحد.'],
            ],
        ];

        $features = [];
        foreach ($featuresData as $data) {
            $features[$data['key']] = Feature::updateOrCreate(
                ['key' => $data['key']],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                ]
            );
        }

        // 2. Seed Plans
        $plansData = [
            [
                'key' => 'essential',
                'name' => ['en' => 'Essential', 'fr' => 'Essentiel', 'ar' => 'الأساسية'],
                'description' => [
                    'en' => 'Perfect for independent professionals looking for a reliable, unlimited digital presence.',
                    'fr' => 'Parfait pour les professionnels indépendants souhaitant une présence numérique illimitée.',
                    'ar' => 'مثالية للمهنيين المستقلين الراغبين في هوية رقمية موثوقة ومفتوحة.',
                ],
                'price' => 99.00,
                'currency' => 'MAD',
                'billing_period' => 'yearly',
                'is_active' => true,
                'is_recommended' => false,
                'sort_order' => 1,
                'features' => [
                    'profile.unlimited_edits' => 'true',
                    'profile.premium_templates' => 'true',
                    'analytics.basic' => 'true',
                    'documents.enabled' => 'true',
                ],
            ],
            [
                'key' => 'pro',
                'name' => ['en' => 'Professional', 'fr' => 'Professionnel', 'ar' => 'الاحترافية'],
                'description' => [
                    'en' => 'Comprehensive digital card suite with advanced blocks, lead capture, and full analytics.',
                    'fr' => 'Suite complète avec blocs interactifs, capture de prospects et analytique avancée.',
                    'ar' => 'باقة متكاملة تشمل التقاط بيانات العملاء والتحليلات المتقدمة والوحدات التفاعلية.',
                ],
                'price' => 199.00,
                'currency' => 'MAD',
                'billing_period' => 'yearly',
                'is_active' => true,
                'is_recommended' => true,
                'sort_order' => 2,
                'features' => [
                    'profile.unlimited_edits' => 'true',
                    'profile.premium_templates' => 'true',
                    'profile.premium_themes' => 'true',
                    'profile.advanced_blocks' => 'true',
                    'analytics.basic' => 'true',
                    'analytics.advanced' => 'true',
                    'leads.enabled' => 'true',
                    'documents.enabled' => 'true',
                ],
            ],
            [
                'key' => 'business',
                'name' => ['en' => 'Business Enterprise', 'fr' => 'Entreprise / Business', 'ar' => 'الأعمال والمؤسسات'],
                'description' => [
                    'en' => 'White-label corporate experience with multi-profile slots and priority ecosystem support.',
                    'fr' => 'Solution marque blanche pour entreprises avec profils multiples.',
                    'ar' => 'حل مؤسسي متكامل بدون علامات تجارية ودعم إدارة ملفات متعددة.',
                ],
                'price' => 349.00,
                'currency' => 'MAD',
                'billing_period' => 'yearly',
                'is_active' => true,
                'is_recommended' => false,
                'sort_order' => 3,
                'features' => [
                    'profile.unlimited_edits' => 'true',
                    'profile.premium_templates' => 'true',
                    'profile.premium_themes' => 'true',
                    'profile.advanced_blocks' => 'true',
                    'analytics.basic' => 'true',
                    'analytics.advanced' => 'true',
                    'leads.enabled' => 'true',
                    'documents.enabled' => 'true',
                    'branding.remove_aura' => 'true',
                    'profile.extra_profiles' => '5',
                ],
            ],
        ];

        foreach ($plansData as $planItem) {
            $plan = Plan::updateOrCreate(
                ['key' => $planItem['key']],
                [
                    'name' => $planItem['name'],
                    'description' => $planItem['description'],
                    'price' => $planItem['price'],
                    'currency' => $planItem['currency'],
                    'billing_period' => $planItem['billing_period'],
                    'is_active' => $planItem['is_active'],
                    'is_recommended' => $planItem['is_recommended'],
                    'sort_order' => $planItem['sort_order'],
                ]
            );

            // Sync features
            $syncData = [];
            foreach ($planItem['features'] as $fKey => $val) {
                if (isset($features[$fKey])) {
                    $syncData[$features[$fKey]->id] = ['value' => $val];
                }
            }
            $plan->features()->sync($syncData);
        }

        // 3. Seed Payment Settings with safe DEMO placeholders (Configurable in Admin)
        PaymentSetting::updateOrCreate(
            ['key' => 'manual_bank_transfer'],
            [
                'title' => [
                    'en' => 'National Bank Wire Transfer',
                    'fr' => 'Virement Bancaire National',
                    'ar' => 'تحويل بنكي وطني',
                ],
                'instructions' => [
                    'en' => 'Transfer the exact order amount to the bank account below. Include your order reference code in the transfer memo, then upload your transfer receipt.',
                    'fr' => 'Transférez le montant exact de votre commande vers le compte bancaire ci-dessous. Mentionnez votre référence de commande dans le motif du virement, puis téléchargez votre reçu.',
                    'ar' => 'قم بتحويل المبلغ الدقيق لطلبك إلى الحساب البنكي الموضح أدناه، مع تضمين رمز مرجع الطلب في خانة الغرض، ثم ارفع صورة وصل التحويل.',
                ],
                'bank_name' => 'Banque Partenaire Démo (Configurable dans Admin)',
                'rib' => '000 000 0000000000000000 00',
                'swift_bic' => 'DEMOMAMC',
                'account_holder' => 'AURA CARD SARL DÉMO',
                'is_enabled' => true,
                'sort_order' => 1,
            ]
        );

        PaymentSetting::updateOrCreate(
            ['key' => 'cash_deposit'],
            [
                'title' => [
                    'en' => 'Cash Agency Deposit',
                    'fr' => 'Versement Espèces en Agence',
                    'ar' => 'إيداع نقدي عبر شبكة الوكالات',
                ],
                'instructions' => [
                    'en' => 'Make a cash deposit at any authorized payment branch nationwide using the reference account details below, then upload the stamped deposit slip.',
                    'fr' => 'Effectuez un versement en espèces dans n\'importe quelle agence bancaire ou réseau de paiement agréé au Maroc, puis téléchargez le bordereau tamponné.',
                    'ar' => 'قم بإيداع المبلغ نقداً في أي فرع بنكي أو شبكة وكالات دفع معتمدة، ثم التقط صورة للوصل أو الإشعار المختوم وارفعها هنا.',
                ],
                'bank_name' => 'Réseau Agences Démo (Configurable dans Admin)',
                'rib' => '000 000 0000000000000000 00',
                'swift_bic' => null,
                'account_holder' => 'AURA CARD SARL DÉMO',
                'is_enabled' => true,
                'sort_order' => 2,
            ]
        );
    }
}
