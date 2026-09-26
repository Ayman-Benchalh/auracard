<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\Profile\CreateProfileForEntitlementAction;
use App\Actions\Profile\PublishProfileVersionAction;
use App\Actions\Profile\SaveProfileBlockAction;
use App\Enums\CardEntitlementStatus;
use App\Enums\CardStatus;
use App\Enums\CardType;
use App\Enums\ProfileBlockType;
use App\Models\CardBatch;
use App\Models\CardEntitlement;
use App\Models\NfcCard;
use App\Models\ProfessionType;
use App\Models\Profile;
use App\Models\Template;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProfileDemoSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::where('email', 'customer@auracard.ma')->first();
        if (! $customer) {
            return;
        }

        // Check if customer already has a profile
        if (Profile::where('user_id', $customer->id)->exists()) {
            return;
        }

        // Ensure customer has a claimed active card
        $entitlement = CardEntitlement::where('user_id', $customer->id)
            ->where('status', CardEntitlementStatus::ACTIVE)
            ->first();

        if (! $entitlement) {
            $batch = CardBatch::firstOrCreate(
                ['batch_code' => 'DEMO-BATCH-01'],
                [
                    'card_type' => CardType::MATTE_BLACK,
                    'quantity' => 10,
                    'notes' => 'Demo batch',
                ]
            );

            $card = NfcCard::create([
                'batch_id' => $batch->id,
                'user_id' => $customer->id,
                'serial_number' => 'AURA-DEMO-0001',
                'public_token_hash' => NfcCard::hashPublicToken('aura_demo_token_32chars_long_xyz123'),
                'claim_code_hash' => null,
                'card_type' => CardType::MATTE_BLACK,
                'status' => CardStatus::ACTIVE,
                'activated_at' => now(),
            ]);

            $entitlement = CardEntitlement::create([
                'source_nfc_card_id' => $card->id,
                'current_nfc_card_id' => $card->id,
                'user_id' => $customer->id,
                'initial_credits_amount' => 2,
                'is_initial_credits_granted' => false,
                'status' => CardEntitlementStatus::ACTIVE,
            ]);
        }

        $profession = ProfessionType::where('key', 'lawyer')->first();
        $template = Template::where('key', 'executive')->first();
        $theme = Theme::where('key', 'midnight_obsidian')->first();

        $createAction = app(CreateProfileForEntitlementAction::class);
        $profile = $createAction->execute($customer, $entitlement, [
            'slug' => 'ayman-el-amrani',
            'display_name' => 'Me Ayman El Amrani',
            'headline' => 'Senior Partner & Commercial Attorney',
            'bio' => 'Advising international enterprises, institutional investors, and tech leaders on corporate structuring and dispute resolution in North Africa.',
            'company' => 'El Amrani & Associates Law Firm',
            'job_title' => 'Managing Partner',
            'location' => 'Casablanca, Morocco',
            'profession_type_id' => $profession?->id,
            'template_id' => $template?->id,
            'theme_id' => $theme?->id,
            'primary_color' => '#d97706',
            'secondary_color' => '#f59e0b',
            'font_family' => 'Playfair Display',
        ]);

        $saveBlock = app(SaveProfileBlockAction::class);

        // Add Contact buttons
        $saveBlock->execute($customer, $profile, [
            'type' => ProfileBlockType::CONTACT_ACTIONS->value,
            'title' => 'Contact Channels',
            'content' => [
                'items' => [
                    ['type' => 'phone', 'label' => 'Direct Chambers', 'value' => '+212 522 99 88 77', 'is_primary' => true],
                    ['type' => 'email', 'label' => 'Confidential Inquiry', 'value' => 'ayman@elamrani-law.ma'],
                    ['type' => 'whatsapp', 'label' => 'WhatsApp Legal', 'value' => '+212 661 00 11 22'],
                    ['type' => 'address', 'label' => 'Casablanca Finance City', 'value' => 'Tower CFC, Floor 14, Casablanca'],
                ],
            ],
        ]);

        // Add Social links
        $saveBlock->execute($customer, $profile, [
            'type' => ProfileBlockType::SOCIAL_LINKS->value,
            'title' => 'Professional Networks',
            'content' => [
                'items' => [
                    ['platform' => 'linkedin', 'url' => 'https://linkedin.com/in/ayman-law', 'username' => 'ayman-law'],
                    ['platform' => 'twitter', 'url' => 'https://x.com/ayman_law', 'username' => 'ayman_law'],
                ],
            ],
        ]);

        // Add Services
        $saveBlock->execute($customer, $profile, [
            'type' => ProfileBlockType::SERVICES->value,
            'title' => 'Key Practices',
            'content' => [
                'items' => [
                    ['name' => 'Corporate Mergers & Acquisitions', 'description' => 'Cross-border M&A advisory, antitrust compliance and due diligence.', 'price' => 'Retainer'],
                    ['name' => 'Arbitration & Commercial Litigation', 'description' => 'International commercial arbitration representation under ICC & CIMAC rules.', 'price' => 'Counsel'],
                    ['name' => 'Fintech & Digital Assets Compliance', 'description' => 'Regulatory positioning before central authorities and license filings.', 'price' => 'Project'],
                ],
            ],
        ]);

        // Add Business Hours
        $saveBlock->execute($customer, $profile, [
            'type' => ProfileBlockType::BUSINESS_HOURS->value,
            'title' => 'Chamber Consultations',
            'content' => [
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
            ],
        ]);

        // Publish profile version
        $publishAction = app(PublishProfileVersionAction::class);
        $publishAction->execute($customer, $profile, 'Initial launch of executive profile');
    }
}
