<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ProfileBlockType;
use App\Models\ProfessionType;
use Illuminate\Database\Seeder;

class ProfessionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $professions = [
            [
                'slug' => 'lawyer',
                'name' => ['en' => 'Lawyer / Attorney', 'fr' => 'Avocat / Juriste', 'ar' => 'محامي / مستشار قانوني'],
                'category' => 'Legal & Corporate',
                'icon' => 'scale',
                'default_blocks' => [
                    ['type' => ProfileBlockType::CONTACT_ACTIONS->value, 'title' => 'Direct Contact', 'content' => ['items' => [
                        ['type' => 'phone', 'label' => 'Office Phone', 'value' => '+212 522 00 00 00', 'is_primary' => true],
                        ['type' => 'email', 'label' => 'Legal Inquiries', 'value' => 'contact@cabinet-avocat.ma'],
                        ['type' => 'address', 'label' => 'Law Firm Office', 'value' => 'Boulevard d\'Anfa, Casablanca'],
                    ]]],
                    ['type' => ProfileBlockType::BIO->value, 'title' => 'Practice Overview', 'content' => [
                        'headline' => 'Advocacy & Legal Consultancy',
                        'bio' => 'Providing high-caliber counsel in corporate law, commercial litigation, and intellectual property protection.',
                    ]],
                    ['type' => ProfileBlockType::SERVICES->value, 'title' => 'Practice Areas', 'content' => ['items' => [
                        ['name' => 'Corporate Governance', 'description' => 'Structuring, compliance, and contract negotiation.'],
                        ['name' => 'Commercial Litigation', 'description' => 'Court representation and dispute mediation.'],
                    ]]],
                ],
            ],
            [
                'slug' => 'doctor',
                'name' => ['en' => 'Medical Doctor', 'fr' => 'Médecin Spécialiste', 'ar' => 'طبيب متخصص'],
                'category' => 'Healthcare',
                'icon' => 'heart',
                'default_blocks' => [
                    ['type' => ProfileBlockType::CONTACT_ACTIONS->value, 'title' => 'Appointments & Desk', 'content' => ['items' => [
                        ['type' => 'phone', 'label' => 'Clinic Reception', 'value' => '+212 537 00 00 00', 'is_primary' => true],
                        ['type' => 'whatsapp', 'label' => 'WhatsApp Desk', 'value' => '+212 600 00 00 00'],
                    ]]],
                    ['type' => ProfileBlockType::BIO->value, 'title' => 'Medical Practice', 'content' => [
                        'headline' => 'Consultant Specialist Physician',
                        'bio' => 'Providing comprehensive, evidence-based healthcare consultations and outpatient treatment.',
                    ]],
                    ['type' => ProfileBlockType::BUSINESS_HOURS->value, 'title' => 'Consultation Hours', 'content' => [
                        'timezone' => 'Africa/Casablanca',
                        'days' => [
                            ['day' => 'monday', 'open' => '09:00', 'close' => '17:00', 'is_closed' => false],
                            ['day' => 'tuesday', 'open' => '09:00', 'close' => '17:00', 'is_closed' => false],
                            ['day' => 'wednesday', 'open' => '09:00', 'close' => '17:00', 'is_closed' => false],
                            ['day' => 'thursday', 'open' => '09:00', 'close' => '17:00', 'is_closed' => false],
                            ['day' => 'friday', 'open' => '09:00', 'close' => '13:00', 'is_closed' => false],
                            ['day' => 'saturday', 'open' => '09:00', 'close' => '12:00', 'is_closed' => false],
                            ['day' => 'sunday', 'open' => null, 'close' => null, 'is_closed' => true],
                        ],
                    ]],
                    ['type' => ProfileBlockType::BOOKING->value, 'title' => 'Book Appointment', 'content' => [
                        'provider' => 'calendly',
                        'booking_url' => 'https://calendly.com/doctor-cabinet',
                        'button_text' => 'Book Medical Consultation',
                    ]],
                ],
            ],
            [
                'slug' => 'architect',
                'name' => ['en' => 'Architect', 'fr' => 'Architecte', 'ar' => 'مهندس معماري'],
                'category' => 'Architecture & Design',
                'icon' => 'home-modern',
                'default_blocks' => [
                    ['type' => ProfileBlockType::PORTFOLIO->value, 'title' => 'Selected Works', 'content' => ['items' => [
                        ['title' => 'Contemporary Villa', 'image_url' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c', 'description' => 'Modern minimalist residence in Marrakech.'],
                    ]]],
                ],
            ],
            [
                'slug' => 'real-estate-agent',
                'name' => ['en' => 'Real Estate Advisor', 'fr' => 'Conseiller Immobilier', 'ar' => 'مستشار عقاري'],
                'category' => 'Real Estate',
                'icon' => 'building-office',
                'default_blocks' => [
                    ['type' => ProfileBlockType::CONTACT_ACTIONS->value, 'title' => 'Contact Advisor', 'content' => ['items' => [
                        ['type' => 'phone', 'label' => 'Direct Mobile', 'value' => '+212 661 00 22 33', 'is_primary' => true],
                        ['type' => 'whatsapp', 'label' => 'WhatsApp Inquiries', 'value' => '+212 661 00 22 33'],
                        ['type' => 'email', 'label' => 'Agency Email', 'value' => 'realestate@auracard.ma'],
                    ]]],
                    ['type' => ProfileBlockType::BIO->value, 'title' => 'Advisory Profile', 'content' => [
                        'headline' => 'Luxury & Commercial Real Estate Advisory',
                        'bio' => 'Assisting institutional clients and private investors in acquiring prime real estate assets across Morocco.',
                    ]],
                    ['type' => ProfileBlockType::PORTFOLIO->value, 'title' => 'Featured Listings', 'content' => ['items' => [
                        ['title' => 'Villa Palmier - Marrakech', 'image_url' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c', 'link_url' => 'https://example.com/property/1', 'description' => '5-bedroom luxury estate with infinity pool.'],
                        ['title' => 'Sea-View Penthouse - Casablanca', 'image_url' => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750', 'link_url' => 'https://example.com/property/2', 'description' => 'Modern penthouse with panoramic ocean terrace.'],
                    ]]],
                    ['type' => ProfileBlockType::LOCATION->value, 'title' => 'Agency Office', 'content' => [
                        'address' => 'Boulevard d\'Anfa, Casablanca, Morocco',
                        'latitude' => 33.589886,
                        'longitude' => -7.632289,
                        'zoom' => 15,
                    ]],
                ],
            ],
            [
                'slug' => 'software-engineer',
                'name' => ['en' => 'Software Developer / Engineer', 'fr' => 'Ingénieur / Développeur Logiciel', 'ar' => 'مهندس ومطور برمجيات'],
                'category' => 'Technology',
                'icon' => 'code-bracket',
                'default_blocks' => [
                    ['type' => ProfileBlockType::CONTACT_ACTIONS->value, 'title' => 'Get in Touch', 'content' => ['items' => [
                        ['type' => 'email', 'label' => 'Direct Email', 'value' => 'engineer@auracard.ma', 'is_primary' => true],
                        ['type' => 'phone', 'label' => 'Mobile', 'value' => '+212 600 11 22 33'],
                    ]]],
                    ['type' => ProfileBlockType::BIO->value, 'title' => 'About Me', 'content' => [
                        'headline' => 'Full-Stack Software Engineer & Architect',
                        'bio' => 'Engineering scalable distributed backends, robust APIs, and modern responsive web applications.',
                    ]],
                    ['type' => ProfileBlockType::SOCIAL_LINKS->value, 'title' => 'Code & Socials', 'content' => ['items' => [
                        ['platform' => 'github', 'url' => 'https://github.com', 'username' => 'developer'],
                        ['platform' => 'linkedin', 'url' => 'https://linkedin.com', 'username' => 'developer'],
                        ['platform' => 'twitter', 'url' => 'https://x.com', 'username' => 'developer'],
                    ]]],
                    ['type' => ProfileBlockType::SKILLS->value, 'title' => 'Core Stack & Skills', 'content' => ['items' => [
                        ['name' => 'TypeScript / React', 'level' => 'Senior'],
                        ['name' => 'PHP / Laravel', 'level' => 'Lead'],
                        ['name' => 'PostgreSQL / MariaDB', 'level' => 'Senior'],
                        ['name' => 'Cloud & Security', 'level' => 'Advanced'],
                    ]]],
                    ['type' => ProfileBlockType::PROJECTS->value, 'title' => 'Featured Projects', 'content' => ['items' => [
                        ['title' => 'Aura NFC Engine', 'url' => 'https://github.com/auracard', 'subtitle' => 'High throughput anti-fraud NFC card engine.'],
                    ]]],
                ],
            ],
            [
                'slug' => 'photographer',
                'name' => ['en' => 'Photographer', 'fr' => 'Photographe', 'ar' => 'مصور فوتوغرافي'],
                'category' => 'Media & Arts',
                'icon' => 'camera',
                'default_blocks' => [],
            ],
            [
                'slug' => 'management-consultant',
                'name' => ['en' => 'Management Consultant', 'fr' => 'Consultant en Gestion', 'ar' => 'مستشار إدارة أعمال'],
                'category' => 'Business Advisory',
                'icon' => 'chart-bar',
                'default_blocks' => [],
            ],
            [
                'slug' => 'graphic-designer',
                'name' => ['en' => 'Creative & UI Designer', 'fr' => 'Designer Graphique & UI', 'ar' => 'مصمم جرافيك وواجهات'],
                'category' => 'Architecture & Design',
                'icon' => 'sparkles',
                'default_blocks' => [],
            ],
            [
                'slug' => 'chartered-accountant',
                'name' => ['en' => 'Chartered Accountant', 'fr' => 'Expert-Comptable', 'ar' => 'خبير محاسب'],
                'category' => 'Finance & Audit',
                'icon' => 'calculator',
                'default_blocks' => [],
            ],
            [
                'slug' => 'fitness-coach',
                'name' => ['en' => 'Personal Fitness Coach', 'fr' => 'Coach Sportif', 'ar' => 'مدرب لياقة بدنية'],
                'category' => 'Sports & Wellness',
                'icon' => 'trophy',
                'default_blocks' => [],
            ],
            [
                'slug' => 'executive-chef',
                'name' => ['en' => 'Executive Chef', 'fr' => 'Chef Cuisinier', 'ar' => 'شيف تنفيذي'],
                'category' => 'Gastronomy',
                'icon' => 'fire',
                'default_blocks' => [],
            ],
            [
                'slug' => 'artist',
                'name' => ['en' => 'Visual Artist', 'fr' => 'Artiste Plasticien', 'ar' => 'فنان تشكيلي'],
                'category' => 'Media & Arts',
                'icon' => 'paint-brush',
                'default_blocks' => [],
            ],
            [
                'slug' => 'dentist',
                'name' => ['en' => 'Dental Surgeon', 'fr' => 'Chirurgien Dentiste', 'ar' => 'طبيب جراح أسنان'],
                'category' => 'Healthcare',
                'icon' => 'sparkles',
                'default_blocks' => [],
            ],
            [
                'slug' => 'journalist',
                'name' => ['en' => 'Journalist & Writer', 'fr' => 'Journaliste & Auteur', 'ar' => 'صحفي وكاتب'],
                'category' => 'Media & Arts',
                'icon' => 'newspaper',
                'default_blocks' => [],
            ],
            [
                'slug' => 'notary',
                'name' => ['en' => 'Notary Public', 'fr' => 'Notaire', 'ar' => 'موثق عدلي'],
                'category' => 'Legal & Corporate',
                'icon' => 'document-check',
                'default_blocks' => [],
            ],
            [
                'slug' => 'certified-electrician',
                'name' => ['en' => 'Certified Electrician', 'fr' => 'Électricien Qualifié', 'ar' => 'تقني كهرباء معتمد'],
                'category' => 'Crafts & Trades',
                'icon' => 'bolt',
                'default_blocks' => [],
            ],
            [
                'slug' => 'plumber-contractor',
                'name' => ['en' => 'Plumbing Contractor', 'fr' => 'Plombier Installateur', 'ar' => 'مهني سباكة وصحي'],
                'category' => 'Crafts & Trades',
                'icon' => 'wrench',
                'default_blocks' => [],
            ],
            [
                'slug' => 'digital-marketer',
                'name' => ['en' => 'Digital Marketing Strategist', 'fr' => 'Spécialiste Marketing Digital', 'ar' => 'خبير تسويق رقمي'],
                'category' => 'Marketing & Growth',
                'icon' => 'megaphone',
                'default_blocks' => [],
            ],
            [
                'slug' => 'videographer',
                'name' => ['en' => 'Videographer & Filmmaker', 'fr' => 'Vidéaste & Réalisateur', 'ar' => 'صانع محتوى وفيديو'],
                'category' => 'Media & Arts',
                'icon' => 'video-camera',
                'default_blocks' => [],
            ],
            [
                'slug' => 'event-planner',
                'name' => ['en' => 'Event Planner & Producer', 'fr' => 'Organisateur d\'Événements', 'ar' => 'منظم حفلات ومؤتمرات'],
                'category' => 'Hospitality & Events',
                'icon' => 'ticket',
                'default_blocks' => [],
            ],
            [
                'slug' => 'insurance-broker',
                'name' => ['en' => 'Insurance Broker', 'fr' => 'Courtier en Assurances', 'ar' => 'وسيط تأمينات'],
                'category' => 'Finance & Audit',
                'icon' => 'shield-check',
                'default_blocks' => [],
            ],
            [
                'slug' => 'pharmacist',
                'name' => ['en' => 'Pharmacist', 'fr' => 'Pharmacien', 'ar' => 'صيدلي'],
                'category' => 'Healthcare',
                'icon' => 'beaker',
                'default_blocks' => [],
            ],
            [
                'slug' => 'veterinarian',
                'name' => ['en' => 'Veterinary Physician', 'fr' => 'Médecin Vétérinaire', 'ar' => 'طبيب بيطري'],
                'category' => 'Healthcare',
                'icon' => 'heart',
                'default_blocks' => [],
            ],
            [
                'slug' => 'physiotherapist',
                'name' => ['en' => 'Physiotherapist', 'fr' => 'Kinésithérapeute', 'ar' => 'أخصائي علاج طبيعي'],
                'category' => 'Healthcare',
                'icon' => 'hand-raised',
                'default_blocks' => [],
            ],
            [
                'slug' => 'certified-translator',
                'name' => ['en' => 'Sworn Translator', 'fr' => 'Traducteur Assermenté', 'ar' => 'مترجم محلف'],
                'category' => 'Language & Education',
                'icon' => 'language',
                'default_blocks' => [],
            ],
            [
                'slug' => 'musician',
                'name' => ['en' => 'Musician & Composer', 'fr' => 'Musicien & Compositeur', 'ar' => 'موسيقي وملحن'],
                'category' => 'Media & Arts',
                'icon' => 'musical-note',
                'default_blocks' => [],
            ],
            [
                'slug' => 'talent-recruiter',
                'name' => ['en' => 'Executive Recruiter', 'fr' => 'Chasseur de Têtes / Recruteur', 'ar' => 'مسؤول توظيف واستقطاب كفاءات'],
                'category' => 'Human Resources',
                'icon' => 'user-group',
                'default_blocks' => [],
            ],
            [
                'slug' => 'financial-advisor',
                'name' => ['en' => 'Financial Wealth Advisor', 'fr' => 'Conseiller en Gestion de Patrimoine', 'ar' => 'مستشار مالي واستثماري'],
                'category' => 'Finance & Audit',
                'icon' => 'banknotes',
                'default_blocks' => [],
            ],
            [
                'slug' => 'educator-tutor',
                'name' => ['en' => 'Professor & Academic Tutor', 'fr' => 'Enseignant & Formateur', 'ar' => 'أستاذ ومحاضر أكاديمي'],
                'category' => 'Language & Education',
                'icon' => 'academic-cap',
                'default_blocks' => [],
            ],
            [
                'slug' => 'startup-founder',
                'name' => ['en' => 'Tech Startup Founder', 'fr' => 'Fondateur de Startup', 'ar' => 'مؤسس شركة ناشئة'],
                'category' => 'Technology & Startups',
                'icon' => 'rocket-launch',
                'default_blocks' => [
                    ['type' => ProfileBlockType::SKILLS->value, 'title' => 'Key Metrics', 'content' => ['items' => [
                        ['name' => 'Seed Funding', 'level' => '$2.5M'],
                        ['name' => 'Active Users', 'level' => '150k+'],
                    ]]],
                ],
            ],
            [
                'slug' => 'restaurant',
                'name' => ['en' => 'Restaurant & Fine Dining', 'fr' => 'Restaurant & Gastronomie', 'ar' => 'مطعم وضيافة'],
                'category' => 'Gastronomy & Hospitality',
                'icon' => 'fire',
                'default_blocks' => [
                    ['type' => ProfileBlockType::CONTACT_ACTIONS->value, 'title' => 'Reservations', 'content' => ['items' => [
                        ['type' => 'phone', 'label' => 'Reservation Desk', 'value' => '+212 522 99 88 77', 'is_primary' => true],
                        ['type' => 'whatsapp', 'label' => 'WhatsApp Bookings', 'value' => '+212 611 22 33 44'],
                    ]]],
                    ['type' => ProfileBlockType::BIO->value, 'title' => 'Culinary Experience', 'content' => [
                        'headline' => 'Fine Dining & Mediterranean Fusion',
                        'bio' => 'An authentic culinary journey celebrating Moroccan heritage with modern gastronomic finesse and seasonal produce.',
                    ]],
                    ['type' => ProfileBlockType::SERVICES->value, 'title' => 'Menu Highlights', 'content' => ['items' => [
                        ['name' => 'Signature Tasting Menu', 'price' => '450 MAD', 'description' => '5-course gastronomic journey curated daily by our Executive Chef.'],
                        ['name' => 'Atlantic Sea Bass Tartare', 'price' => '180 MAD', 'description' => 'Fresh catch infused with citrus, argan oil, and caper tapenade.'],
                    ]]],
                    ['type' => ProfileBlockType::LOCATION->value, 'title' => 'Location & Valet', 'content' => [
                        'address' => 'Boulevard d\'Anfa, Gauthier, Casablanca, Morocco',
                        'latitude' => 33.589886,
                        'longitude' => -7.632289,
                        'zoom' => 15,
                    ]],
                    ['type' => ProfileBlockType::BUSINESS_HOURS->value, 'title' => 'Dining Hours', 'content' => [
                        'timezone' => 'Africa/Casablanca',
                        'days' => [
                            ['day' => 'monday', 'open' => '12:00', 'close' => '23:30', 'is_closed' => false],
                            ['day' => 'tuesday', 'open' => '12:00', 'close' => '23:30', 'is_closed' => false],
                            ['day' => 'wednesday', 'open' => '12:00', 'close' => '23:30', 'is_closed' => false],
                            ['day' => 'thursday', 'open' => '12:00', 'close' => '23:30', 'is_closed' => false],
                            ['day' => 'friday', 'open' => '12:00', 'close' => '00:00', 'is_closed' => false],
                            ['day' => 'saturday', 'open' => '12:00', 'close' => '00:00', 'is_closed' => false],
                            ['day' => 'sunday', 'open' => '12:00', 'close' => '17:00', 'is_closed' => false],
                        ],
                    ]],
                    ['type' => ProfileBlockType::GALLERY->value, 'title' => 'Ambiance', 'content' => ['items' => [
                        ['image_url' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4', 'caption' => 'Main Dining Room'],
                    ]]],
                ],
            ],
            [
                'slug' => 'freelancer',
                'name' => ['en' => 'Freelancer / Independent Consultant', 'fr' => 'Freelance / Consultant Indépendant', 'ar' => 'مستقل / استشاري حر'],
                'category' => 'Creative & Digital',
                'icon' => 'sparkles',
                'default_blocks' => [
                    ['type' => ProfileBlockType::CONTACT_ACTIONS->value, 'title' => 'Hire Me', 'content' => ['items' => [
                        ['type' => 'email', 'label' => 'Project Inquiries', 'value' => 'freelance@auracard.ma', 'is_primary' => true],
                        ['type' => 'whatsapp', 'label' => 'Direct WhatsApp', 'value' => '+212 655 44 33 22'],
                    ]]],
                    ['type' => ProfileBlockType::BIO->value, 'title' => 'Independent Consultant', 'content' => [
                        'headline' => 'Product Designer & Brand Strategist',
                        'bio' => 'Partnering with ambitious founders and creative studios to design iconic digital products and brands.',
                    ]],
                    ['type' => ProfileBlockType::SOCIAL_LINKS->value, 'title' => 'Online Presence', 'content' => ['items' => [
                        ['platform' => 'linkedin', 'url' => 'https://linkedin.com', 'username' => 'freelancer'],
                        ['platform' => 'github', 'url' => 'https://github.com', 'username' => 'freelancer'],
                    ]]],
                    ['type' => ProfileBlockType::SERVICES->value, 'title' => 'Services & Offerings', 'content' => ['items' => [
                        ['name' => 'UI/UX Design Sprint', 'price' => 'From 15,000 MAD', 'description' => 'Fast-paced concept design, prototypes, and user flows.'],
                        ['name' => 'Full Brand Identity', 'price' => 'From 20,000 MAD', 'description' => 'Logo system, design guidelines, and typography.'],
                    ]]],
                    ['type' => ProfileBlockType::PORTFOLIO->value, 'title' => 'Recent Work', 'content' => ['items' => [
                        ['title' => 'FinTech Mobile Suite', 'image_url' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71', 'link_url' => 'https://dribbble.com', 'description' => 'Mobile banking experience.'],
                    ]]],
                    ['type' => ProfileBlockType::TESTIMONIALS->value, 'title' => 'Client Endorsements', 'content' => ['items' => [
                        ['author' => 'Karim B.', 'role' => 'Managing Director', 'company' => 'FinTech MA', 'content' => 'Delivered top-tier work on tight deadlines. Highly recommended!', 'rating' => 5],
                    ]]],
                ],
            ],
            [
                'slug' => 'company',
                'name' => ['en' => 'Company / Corporate Enterprise', 'fr' => 'Entreprise / Société', 'ar' => 'شركة / مؤسسة تجارية'],
                'category' => 'Business & Enterprise',
                'icon' => 'building-office-2',
                'default_blocks' => [
                    ['type' => ProfileBlockType::CONTACT_ACTIONS->value, 'title' => 'Corporate Contacts', 'content' => ['items' => [
                        ['type' => 'phone', 'label' => 'Headquarters', 'value' => '+212 522 10 20 30', 'is_primary' => true],
                        ['type' => 'email', 'label' => 'General Inquiries', 'value' => 'contact@corporate.ma'],
                    ]]],
                    ['type' => ProfileBlockType::BIO->value, 'title' => 'Corporate Overview', 'content' => [
                        'headline' => 'Transforming Digital Operations',
                        'bio' => 'Engineering scalable technologies, enterprise software, and secure infrastructure for market leaders.',
                    ]],
                    ['type' => ProfileBlockType::SERVICES->value, 'title' => 'Enterprise Capabilities', 'content' => ['items' => [
                        ['name' => 'Enterprise Cloud Migration', 'description' => 'Modernizing legacy workloads into fault-tolerant distributed cloud stacks.'],
                        ['name' => 'Cybersecurity & Compliance', 'description' => 'Auditing, ISO 27001 readiness, and zero-trust perimeter defense.'],
                    ]]],
                    ['type' => ProfileBlockType::LOCATION->value, 'title' => 'Headquarters', 'content' => [
                        'address' => 'Casablanca Finance City, Casablanca, Morocco',
                        'latitude' => 33.5684,
                        'longitude' => -7.6534,
                        'zoom' => 15,
                    ]],
                    ['type' => ProfileBlockType::BUSINESS_HOURS->value, 'title' => 'Office Hours', 'content' => [
                        'timezone' => 'Africa/Casablanca',
                        'days' => [
                            ['day' => 'monday', 'open' => '08:30', 'close' => '18:00', 'is_closed' => false],
                            ['day' => 'tuesday', 'open' => '08:30', 'close' => '18:00', 'is_closed' => false],
                            ['day' => 'wednesday', 'open' => '08:30', 'close' => '18:00', 'is_closed' => false],
                            ['day' => 'thursday', 'open' => '08:30', 'close' => '18:00', 'is_closed' => false],
                            ['day' => 'friday', 'open' => '08:30', 'close' => '17:00', 'is_closed' => false],
                            ['day' => 'saturday', 'open' => null, 'close' => null, 'is_closed' => true],
                            ['day' => 'sunday', 'open' => null, 'close' => null, 'is_closed' => true],
                        ],
                    ]],
                    ['type' => ProfileBlockType::DOCUMENTS->value, 'title' => 'Corporate Deck', 'content' => ['items' => [
                        ['title' => 'Aura Enterprise Presentation 2026', 'file_url' => 'https://example.com/company-deck.pdf', 'file_size_formatted' => '2.8 MB', 'file_type' => 'pdf'],
                    ]]],
                ],
            ],
        ];

        foreach ($professions as $data) {
            ProfessionType::updateOrCreate(
                ['key' => $data['slug']],
                [
                    'name' => $data['name'],
                    'description' => ['en' => $data['category']],
                    'icon' => $data['icon'],
                    'default_blocks' => $data['default_blocks'],
                    'is_active' => true,
                ]
            );
        }
    }
}
