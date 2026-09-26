<?php

declare(strict_types=1);

namespace Tests\Feature\Commercial;

use App\Actions\Commercial\ApprovePaymentRequestAction;
use App\Actions\Commercial\RejectPaymentRequestAction;
use App\Actions\Profile\CreateProfileForEntitlementAction;
use App\Actions\Profile\PublishProfileVersionAction;
use App\Actions\Profile\UpdateProfileDraftAction;
use App\Enums\CardEntitlementStatus;
use App\Enums\CardStatus;
use App\Enums\PaymentRequestStatus;
use App\Enums\RoleName;
use App\Enums\SubscriptionStatus;
use App\Models\AuditLog;
use App\Models\CardEntitlement;
use App\Models\NfcCard;
use App\Models\PaymentRequest;
use App\Models\Plan;
use App\Models\Profile;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CommercialEngineTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;
    protected Plan $proPlan;
    protected CardEntitlement $entitlement;
    protected Profile $profile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('local');
        Notification::fake();

        $this->user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->admin = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $superAdminRole = Role::where('name', RoleName::SUPER_ADMIN->value)->first();
        if ($superAdminRole) {
            $this->admin->roles()->attach($superAdminRole->id);
        }

        $this->proPlan = Plan::where('key', 'pro')->firstOrFail();

        // Setup Card & Entitlement
        $card = NfcCard::create([
            'batch_id' => null,
            'user_id' => $this->user->id,
            'serial_number' => 'AURA-COMM-0001',
            'public_token_hash' => NfcCard::hashPublicToken('aura_comm_token_hash_32chars_test'),
            'claim_code_hash' => null,
            'card_type' => 'matte_black',
            'status' => CardStatus::ACTIVE,
            'activated_at' => now(),
        ]);

        $this->entitlement = CardEntitlement::create([
            'source_nfc_card_id' => $card->id,
            'current_nfc_card_id' => $card->id,
            'user_id' => $this->user->id,
            'initial_credits_amount' => 2,
            'is_initial_credits_granted' => false,
            'status' => CardEntitlementStatus::ACTIVE,
        ]);

        $createAction = app(CreateProfileForEntitlementAction::class);
        $this->profile = $createAction->execute($this->user, $this->entitlement, [
            'display_name' => 'Commercial Test User',
            'slug' => 'commercial-test-user',
        ]);
    }

    public function test_first_publication_grants_two_credits_and_subsequent_changed_publish_deducts_credit(): void
    {
        $publishAction = app(PublishProfileVersionAction::class);
        $updateDraftAction = app(UpdateProfileDraftAction::class);

        // First publication: awards +2 credits, costs 0 credits
        $res1 = $publishAction->execute($this->user, $this->profile);
        $v1 = $res1['version'];

        $this->user->refresh();
        $this->entitlement->refresh();

        $this->assertSame(1, $v1->version_number);
        $this->assertTrue($this->entitlement->is_initial_credits_granted);
        $this->assertSame(2, $this->user->edit_credit_balance);

        // Subsequent publication without any changes: costs 0 credits
        $res1Repeat = $publishAction->execute($this->user, $this->profile);
        $this->user->refresh();
        $this->assertSame('unchanged', $res1Repeat['status']);
        $this->assertSame(2, $this->user->edit_credit_balance);

        // Update draft with real change
        $updateDraftAction->execute($this->user, $this->profile, [
            'headline' => 'Founder & CEO of FinTech Maroc',
        ]);

        // Publish with change: costs 1 credit
        $res2 = $publishAction->execute($this->user, $this->profile);
        $v2 = $res2['version'];

        $this->user->refresh();
        $this->assertSame(2, $v2->version_number);
        $this->assertSame(1, $this->user->edit_credit_balance);

        // Update draft again & publish: consumes remaining 1 credit
        $updateDraftAction->execute($this->user, $this->profile, [
            'headline' => 'Serial Entrepreneur',
        ]);
        $res3 = $publishAction->execute($this->user, $this->profile);
        $v3 = $res3['version'];

        $this->user->refresh();
        $this->assertSame(3, $v3->version_number);
        $this->assertSame(0, $this->user->edit_credit_balance);

        // Now with 0 credits, modifying draft and attempting to publish must throw ValidationException
        $updateDraftAction->execute($this->user, $this->profile, [
            'headline' => 'Investor & Advisor',
        ]);

        $this->expectException(ValidationException::class);
        $publishAction->execute($this->user, $this->profile);
    }

    public function test_active_unlimited_subscription_allows_changed_publishing_at_zero_cost(): void
    {
        $publishAction = app(PublishProfileVersionAction::class);
        $updateDraftAction = app(UpdateProfileDraftAction::class);

        // First publish to get to published baseline
        $publishAction->execute($this->user, $this->profile);
        $this->user->refresh();
        $this->assertSame(2, $this->user->edit_credit_balance);

        // Create active subscription with Pro plan (has profile.unlimited_edits)
        Subscription::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->proPlan->id,
            'status' => SubscriptionStatus::ACTIVE,
            'price_amount' => $this->proPlan->price,
            'price_currency' => 'MAD',
            'billing_period' => 'yearly',
            'plan_snapshot' => [
                'plan_key' => $this->proPlan->key,
                'features' => [
                    'profile.unlimited_edits' => 'true',
                ],
            ],
            'starts_at' => now(),
            'ends_at' => now()->addYear(),
        ]);

        // Make changes to draft
        $updateDraftAction->execute($this->user, $this->profile, [
            'headline' => 'Unlimited Pro Member',
            'bio' => 'Aura Pro Subscriber in Casablanca.',
        ]);

        // Publish: cost is 0 because user has unlimited subscription
        $res2 = $publishAction->execute($this->user, $this->profile);
        $v2 = $res2['version'];
        $this->user->refresh();

        $this->assertSame(2, $v2->version_number);
        // Credits must NOT have been deducted!
        $this->assertSame(2, $this->user->edit_credit_balance);
    }

    public function test_customer_can_submit_payment_request_with_valid_proof(): void
    {
        $file = UploadedFile::fake()->createWithContent(
            'bank-receipt.pdf',
            "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF"
        );

        $response = $this->actingAs($this->user)
            ->post("/checkout/{$this->proPlan->id}", [
                'payment_method' => 'bank_transfer',
                'reference_number' => 'VIR-ATTIJARI-88412',
                'customer_notes' => 'Payment from BMCE corporate account',
                'proof' => $file,
            ]);

        $response->assertRedirect(route('subscription.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('payment_requests', [
            'user_id' => $this->user->id,
            'plan_id' => $this->proPlan->id,
            'payment_method' => 'bank_transfer',
            'reference_number' => 'VIR-ATTIJARI-88412',
            'status' => PaymentRequestStatus::PENDING->value,
        ]);

        $paymentRequest = PaymentRequest::where('reference_number', 'VIR-ATTIJARI-88412')->firstOrFail();
        $this->assertNotNull($paymentRequest->proof_path);
        Storage::disk('local')->assertExists($paymentRequest->proof_path);
    }

    public function test_payment_proof_submission_rejects_disallowed_mimes(): void
    {
        $malicious = UploadedFile::fake()->create('exploit.php', 100, 'application/x-php');

        $response = $this->actingAs($this->user)
            ->post("/checkout/{$this->proPlan->id}", [
                'payment_method' => 'bank_transfer',
                'reference_number' => 'FAKE-001',
                'proof' => $malicious,
            ]);

        $response->assertSessionHasErrors('proof');
    }

    public function test_unauthorized_user_cannot_access_payment_proof(): void
    {
        $file = UploadedFile::fake()->create('proof.pdf', 300, 'application/pdf');
        $path = $file->store('private/payment_proofs', 'local');

        $paymentRequest = PaymentRequest::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->proPlan->id,
            'amount' => 199.00,
            'currency' => 'MAD',
            'payment_method' => 'bank_transfer',
            'reference_number' => 'PRIVATE-PROOF-001',
            'status' => PaymentRequestStatus::PENDING,
            'proof_path' => $path,
            'proof_mime' => 'application/pdf',
            'proof_size' => 300,
            'proof_original_name' => 'proof.pdf',
        ]);

        $otherUser = User::factory()->create();

        // Other customer accessing proof: 403 Forbidden
        $response = $this->actingAs($otherUser)
            ->get("/payment-requests/{$paymentRequest->id}/proof");

        $response->assertForbidden();

        // Owner accessing proof: 200 OK
        $ownerResponse = $this->actingAs($this->user)
            ->get("/payment-requests/{$paymentRequest->id}/proof");

        $ownerResponse->assertOk();

        // Admin accessing proof: 200 OK
        $adminResponse = $this->actingAs($this->admin)
            ->get("/payment-requests/{$paymentRequest->id}/proof");

        $adminResponse->assertOk();
    }

    public function test_admin_can_approve_payment_request_idempotently(): void
    {
        $paymentRequest = PaymentRequest::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->proPlan->id,
            'amount' => 199.00,
            'currency' => 'MAD',
            'payment_method' => 'bank_transfer',
            'reference_number' => 'APPROVAL-TEST-001',
            'status' => PaymentRequestStatus::PENDING,
            'proof_path' => 'private/payment_proofs/test.pdf',
            'proof_mime' => 'application/pdf',
            'proof_size' => 100,
        ]);

        $approveAction = app(ApprovePaymentRequestAction::class);
        $subscription = $approveAction->execute($this->admin, $paymentRequest);

        $paymentRequest->refresh();
        $this->assertSame(PaymentRequestStatus::APPROVED, $paymentRequest->status);
        $this->assertNotNull($paymentRequest->processed_at);
        $this->assertSame($this->admin->id, $paymentRequest->processed_by);

        $this->assertInstanceOf(Subscription::class, $subscription);
        $this->assertSame(SubscriptionStatus::ACTIVE, $subscription->status);
        $this->assertSame($this->user->id, $subscription->user_id);
        $this->assertNotNull($subscription->plan_snapshot);
        $this->assertSame('pro', $subscription->plan_snapshot['key']);

        // Check AuditLog was created
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'payment.approved',
            'actor_id' => $this->admin->id,
            'target_type' => PaymentRequest::class,
            'target_id' => $paymentRequest->id,
        ]);

        // Idempotency: second approval attempt must throw ValidationException
        $this->expectException(ValidationException::class);
        $approveAction->execute($this->admin, $paymentRequest);
    }

    public function test_admin_can_reject_payment_request_with_mandatory_reason(): void
    {
        $paymentRequest = PaymentRequest::create([
            'user_id' => $this->user->id,
            'plan_id' => $this->proPlan->id,
            'amount' => 199.00,
            'currency' => 'MAD',
            'payment_method' => 'bank_transfer',
            'reference_number' => 'REJECT-TEST-001',
            'status' => PaymentRequestStatus::PENDING,
            'proof_path' => 'private/payment_proofs/test.pdf',
            'proof_mime' => 'application/pdf',
            'proof_size' => 100,
        ]);

        $rejectAction = app(RejectPaymentRequestAction::class);
        $rejectAction->execute($this->admin, $paymentRequest, 'Unreadable receipt screenshot, please resubmit with full RIB.');

        $paymentRequest->refresh();
        $this->assertSame(PaymentRequestStatus::REJECTED, $paymentRequest->status);
        $this->assertSame('Unreadable receipt screenshot, please resubmit with full RIB.', $paymentRequest->rejection_reason);
        $this->assertSame($this->admin->id, $paymentRequest->processed_by);

        // Check AuditLog
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'payment.rejected',
            'actor_id' => $this->admin->id,
            'target_id' => $paymentRequest->id,
        ]);
    }
}
