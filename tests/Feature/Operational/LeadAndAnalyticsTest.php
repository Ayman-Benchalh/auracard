<?php

declare(strict_types=1);

namespace Tests\Feature\Operational;

use App\Actions\Profile\CreateProfileForEntitlementAction;
use App\Actions\Profile\PublishProfileVersionAction;
use App\Enums\CardEntitlementStatus;
use App\Enums\CardStatus;
use App\Models\CardEntitlement;
use App\Models\NfcCard;
use App\Models\Profile;
use App\Models\ProfileAnalyticsEvent;
use App\Models\ProfileLead;
use App\Models\User;
use App\Notifications\ProfileLeadReceivedNotification;
use App\Services\Analytics\ProfileAnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LeadAndAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected CardEntitlement $entitlement;
    protected Profile $profile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Notification::fake();

        $this->owner = User::factory()->create([
            'email_verified_at' => now(),
            'name' => 'Dr. Leila Bennani',
            'email' => 'leila.bennani@auracard.ma',
        ]);

        $card = NfcCard::create([
            'batch_id' => null,
            'user_id' => $this->owner->id,
            'serial_number' => 'AURA-LEAD-0001',
            'public_token_hash' => NfcCard::hashPublicToken('aura_lead_token_hash_32chars_test'),
            'claim_code_hash' => null,
            'card_type' => 'matte_black',
            'status' => CardStatus::ACTIVE,
            'activated_at' => now(),
        ]);

        $this->entitlement = CardEntitlement::create([
            'source_nfc_card_id' => $card->id,
            'current_nfc_card_id' => $card->id,
            'user_id' => $this->owner->id,
            'initial_credits_amount' => 2,
            'is_initial_credits_granted' => false,
            'status' => CardEntitlementStatus::ACTIVE,
        ]);

        $createAction = app(CreateProfileForEntitlementAction::class);
        $this->profile = $createAction->execute($this->owner, $this->entitlement, [
            'display_name' => 'Dr. Leila Bennani',
            'slug' => 'dr-leila-bennani',
        ]);

        $publishAction = app(PublishProfileVersionAction::class);
        $publishAction->execute($this->owner, $this->profile);
    }

    public function test_visitor_can_submit_contact_lead_and_owner_is_notified(): void
    {
        $response = $this->postJson("/p/{$this->profile->slug}/contact", [
            'name' => 'Karim Idrissi',
            'email' => 'karim.idrissi@example.com',
            'phone' => '+212661234567',
            'message' => 'Bonjour Dr. Bennani, je souhaite prendre rendez-vous pour une consultation.',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        // Check lead is saved
        $this->assertDatabaseHas('profile_leads', [
            'profile_id' => $this->profile->id,
            'name' => 'Karim Idrissi',
            'email' => 'karim.idrissi@example.com',
            'phone' => '+212661234567',
        ]);

        // Check owner notification was dispatched
        Notification::assertSentTo(
            $this->owner,
            ProfileLeadReceivedNotification::class,
            function (ProfileLeadReceivedNotification $notification) {
                return $notification->lead->name === 'Karim Idrissi';
            }
        );
    }

    public function test_honeypot_silently_drops_spam_bots_without_storing(): void
    {
        $response = $this->postJson("/p/{$this->profile->slug}/contact", [
            'name' => 'Bot Spammer',
            'email' => 'bot@spammer.org',
            'message' => 'Buy cheap links now!',
            'website_trap' => 'http://spam-link.com', // Honeypot filled by bot
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        // Must NOT exist in database!
        $this->assertDatabaseMissing('profile_leads', [
            'name' => 'Bot Spammer',
        ]);

        Notification::assertNothingSent();
    }

    public function test_privacy_preserving_analytics_telemetry_recording(): void
    {
        // 1. Visit profile page
        $this->get("/p/{$this->profile->slug}");

        // 2. Track link click via API
        $response = $this->postJson("/p/{$this->profile->slug}/events", [
            'event_type' => 'link_click',
            'target_url' => 'https://linkedin.com/in/leilabennani',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        // 3. Download vCard
        $this->get("/p/{$this->profile->slug}/vcard");

        // Verify events recorded
        $this->assertDatabaseHas('profile_analytics_events', [
            'profile_id' => $this->profile->id,
            'event_type' => 'view',
        ]);

        $this->assertDatabaseHas('profile_analytics_events', [
            'profile_id' => $this->profile->id,
            'event_type' => 'link_click',
            'target_url' => 'https://linkedin.com/in/leilabennani',
        ]);

        $this->assertDatabaseHas('profile_analytics_events', [
            'profile_id' => $this->profile->id,
            'event_type' => 'vcard_download',
        ]);

        // Check that raw IP is NOT stored in any column
        $event = ProfileAnalyticsEvent::where('profile_id', $this->profile->id)->firstOrFail();
        $this->assertNotNull($event->visitor_hash);
        $this->assertNotSame('127.0.0.1', $event->visitor_hash);

        // Verify analytics service aggregation
        $analyticsService = app(ProfileAnalyticsService::class);
        $metrics = $analyticsService->getProfileMetrics($this->profile, 30);

        $this->assertGreaterThanOrEqual(1, $metrics['total_views']);
        $this->assertGreaterThanOrEqual(1, $metrics['action_clicks']);
        $this->assertGreaterThanOrEqual(1, $metrics['vcard_downloads']);
        $this->assertGreaterThanOrEqual(1, $metrics['unique_visitors']);
    }
}
