<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Enums\EditCreditReason;
use App\Enums\ProfileStatus;
use App\Models\CardEntitlement;
use App\Models\EditCreditTransaction;
use App\Models\Profile;
use App\Models\ProfileVersion;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\Commercial\ProfileEntitlementResolver;
use App\Services\Profile\CanonicalSnapshotService;
use App\Services\VCard\VCard4Generator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PublishProfileVersionAction
{
    public function __construct(
        protected CanonicalSnapshotService $snapshotService,
        protected VCard4Generator $vcardGenerator,
        protected AuditLogService $auditLogService,
        protected ProfileEntitlementResolver $entitlementResolver
    ) {}

    /**
     * Atomically publish a draft profile or update to a new immutable version.
     * Enforces draft/published separation, first-publish free +2 bonus credits,
     * credit checks on subsequent edits, and double-publish idempotency.
     *
     * @param User $user
     * @param Profile $profile
     * @param string|null $changeSummary
     * @return array{profile: Profile, version: ProfileVersion, status: string, credits_deducted: int, credits_awarded: int}
     * @throws ValidationException
     */
    public function execute(User $user, Profile $profile, ?string $changeSummary = null): array
    {
        return DB::transaction(function () use ($user, $profile, $changeSummary) {
            // Lock profile row
            $lockedProfile = Profile::where('id', $profile->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedProfile->user_id !== $user->id) {
                throw ValidationException::withMessages([
                    'profile' => [__('You do not own this profile.')],
                ]);
            }

            if ($lockedProfile->status === ProfileStatus::Suspended) {
                throw ValidationException::withMessages([
                    'profile' => [__('This profile has been suspended and cannot be published.')],
                ]);
            }

            if ($lockedProfile->status === ProfileStatus::Archived) {
                throw ValidationException::withMessages([
                    'profile' => [__('This profile has been archived and cannot be published.')],
                ]);
            }

            // Lock entitlement if bound
            $entitlement = null;
            if ($lockedProfile->card_entitlement_id) {
                $entitlement = CardEntitlement::where('id', $lockedProfile->card_entitlement_id)
                    ->lockForUpdate()
                    ->first();
            }

            // Generate canonical snapshot from current draft
            $blocks = $lockedProfile->blocks()
                ->where('is_enabled', true)
                ->orderBy('sort_order', 'asc')
                ->get();

            $snapshot = $this->snapshotService->buildSnapshot($lockedProfile, $blocks);
            $snapshotHash = $this->snapshotService->calculateHash($snapshot);

            $isFirstPublish = ((int) $lockedProfile->current_version === 0);
            $creditsDeducted = 0;
            $creditsAwarded = 0;

            if ($isFirstPublish) {
                // First publication: 0 credits deducted
                // Atomically award dynamic initial credits from entitlement if entitlement hasn't received them yet
                if ($entitlement && ! $entitlement->is_initial_credits_granted) {
                    $grantAmount = (int) ($entitlement->initial_credits_amount > 0 ? $entitlement->initial_credits_amount : 2);
                    $newBalance = $user->edit_credit_balance + $grantAmount;
                    EditCreditTransaction::create([
                        'user_id' => $user->id,
                        'card_entitlement_id' => $entitlement->id,
                        'profile_id' => $lockedProfile->id,
                        'amount' => $grantAmount,
                        'balance_after' => $newBalance,
                        'reason' => EditCreditReason::INITIAL_GRANT,
                        'metadata' => [
                            'profile_id' => $lockedProfile->id,
                            'note' => 'First publication quota grant',
                            'granted_amount' => $grantAmount,
                        ],
                        'created_at' => now(),
                    ]);

                    $entitlement->is_initial_credits_granted = true;
                    $entitlement->initial_credits_granted_at = now();
                    $entitlement->save();

                    $creditsAwarded = $grantAmount;
                }
            } else {
                // Check if snapshot hash differs from current active version
                $currentVersion = ProfileVersion::where('profile_id', $lockedProfile->id)
                    ->where('version_number', $lockedProfile->current_version)
                    ->first();

                $isContentChanged = (! $currentVersion || $currentVersion->content_hash !== $snapshotHash);

                $decision = $this->entitlementResolver->resolvePublishDecision($user, $lockedProfile, $isContentChanged);

                if (! $decision['allowed']) {
                    throw ValidationException::withMessages([
                        'credits' => [$decision['message'] ?? __('Insufficient edit credits. Please acquire more credits or subscribe to publish changes.')],
                    ]);
                }

                if ($decision['reason'] === 'unchanged_content') {
                    // No changes detected: return unchanged without consuming credits
                    return [
                        'profile' => $lockedProfile,
                        'version' => $currentVersion,
                        'status' => 'unchanged',
                        'credits_deducted' => 0,
                        'credits_awarded' => 0,
                    ];
                }

                // If cost > 0, deduct credits; if cost === 0 (unlimited subscription), deduct 0
                if ($decision['cost'] > 0) {
                    $currentBalance = $user->edit_credit_balance;
                    $newBalance = $currentBalance - $decision['cost'];

                    EditCreditTransaction::create([
                        'user_id' => $user->id,
                        'card_entitlement_id' => $entitlement?->id,
                        'profile_id' => $lockedProfile->id,
                        'amount' => -$decision['cost'],
                        'balance_after' => $newBalance,
                        'reason' => EditCreditReason::PROFILE_PUBLISH,
                        'metadata' => [
                            'profile_id' => $lockedProfile->id,
                            'new_version' => $lockedProfile->version_counter + 1,
                            'change_summary' => $changeSummary,
                        ],
                        'created_at' => now(),
                    ]);

                    $creditsDeducted = $decision['cost'];
                }
            }

            // Generate cached vCard 4.0 representation
            $vcardString = $this->vcardGenerator->generate($lockedProfile, $blocks);

            // Create new immutable profile version
            $newVersionNumber = $lockedProfile->version_counter + 1;

            $version = ProfileVersion::create([
                'profile_id' => $lockedProfile->id,
                'version_number' => $newVersionNumber,
                'content_snapshot' => $snapshot,
                'design_snapshot' => $lockedProfile->draft_design_tokens ?? [],
                'content_hash' => $snapshotHash,
                'published_by_user_id' => $user->id,
                'published_at' => now(),
            ]);

            // Update profile with published version link
            $lockedProfile->status = ProfileStatus::PUBLISHED;
            $lockedProfile->published_at = now();
            if (! $lockedProfile->first_published_at) {
                $lockedProfile->first_published_at = now();
            }
            $lockedProfile->current_version = $newVersionNumber;
            $tokens = $lockedProfile->draft_design_tokens ?? [];
            $tokens['vcard_cache'] = $vcardString;
            $lockedProfile->published_design_tokens = $tokens;
            $lockedProfile->save();

            // Log audit
            $this->auditLogService->log(
                'profile.published',
                $lockedProfile,
                ['status' => $lockedProfile->getOriginal('status'), 'version_counter' => $lockedProfile->getOriginal('current_version')],
                ['status' => ProfileStatus::PUBLISHED->value, 'version_counter' => $newVersionNumber, 'version_id' => $version->id],
                "Published profile version {$newVersionNumber}",
                $user
            );

            return [
                'profile' => $lockedProfile->fresh(),
                'version' => $version,
                'status' => 'published',
                'credits_deducted' => $creditsDeducted,
                'credits_awarded' => $creditsAwarded,
            ];
        });
    }
}
