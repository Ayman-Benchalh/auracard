<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Enums\CardEntitlementStatus;
use App\Enums\ProfileBlockType;
use App\Enums\ProfileStatus;
use App\Models\CardEntitlement;
use App\Models\ProfessionType;
use App\Models\Profile;
use App\Models\ProfileBlock;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\Profile\ProfileSlugService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateProfileForEntitlementAction
{
    public function __construct(
        protected ProfileSlugService $slugService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Create a new draft profile bound to an active physical card entitlement.
     * Enforces the invariant: One eligible physical card entitlement = one primary profile.
     *
     * @param User $user
     * @param CardEntitlement $entitlement
     * @param array<string, mixed> $data
     * @return Profile
     * @throws ValidationException
     */
    public function execute(User $user, CardEntitlement $entitlement, array $data): Profile
    {
        return DB::transaction(function () use ($user, $entitlement, $data) {
            // Lock entitlement
            $lockedEntitlement = CardEntitlement::where('id', $entitlement->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedEntitlement->user_id !== $user->id) {
                throw ValidationException::withMessages([
                    'entitlement' => [__('You do not own this card entitlement.')],
                ]);
            }

            if ($lockedEntitlement->status !== CardEntitlementStatus::ACTIVE) {
                throw ValidationException::withMessages([
                    'entitlement' => [__('Only active card entitlements can have an active profile.')],
                ]);
            }

            // Check if profile already exists for this entitlement
            if ($lockedEntitlement->profile_id !== null || Profile::where('card_entitlement_id', $lockedEntitlement->id)->exists()) {
                throw ValidationException::withMessages([
                    'entitlement' => [__('This card entitlement already has an associated profile.')],
                ]);
            }

            $displayName = trim((string) ($data['display_name'] ?? $user->name));
            $desiredSlug = ! empty($data['slug']) ? (string) $data['slug'] : $displayName;
            $slug = $this->slugService->generateUniqueSlug($desiredSlug);

            $professionTypeId = ! empty($data['profession_type_id']) ? (string) $data['profession_type_id'] : null;
            $templateId = ! empty($data['template_id']) ? (string) $data['template_id'] : null;
            $themeId = ! empty($data['theme_id']) ? (string) $data['theme_id'] : null;

            // Check if this is user's first profile
            $isFirstProfile = ! Profile::where('user_id', $user->id)->exists();

            $profile = Profile::create([
                'user_id' => $user->id,
                'card_entitlement_id' => $lockedEntitlement->id,
                'slug' => $slug,
                'display_name' => $displayName,
                'professional_title' => $data['headline'] ?? $data['professional_title'] ?? null,
                'bio' => $data['bio'] ?? null,
                'company_name' => $data['company'] ?? $data['company_name'] ?? null,
                'profession_type_id' => $professionTypeId,
                'template_id' => $templateId,
                'theme_id' => $themeId,
                'status' => ProfileStatus::DRAFT,
                'current_version' => 0,
                'draft_design_tokens' => [
                    'avatar_url' => $data['avatar_url'] ?? null,
                    'cover_url' => $data['cover_url'] ?? null,
                    'primary_color' => $data['primary_color'] ?? '#1e293b',
                    'secondary_color' => $data['secondary_color'] ?? '#3b82f6',
                    'font_family' => $data['font_family'] ?? 'Inter',
                    'location' => $data['location'] ?? null,
                    'custom_css' => $data['custom_css'] ?? null,
                ],
            ]);

            // Link entitlement to profile
            $lockedEntitlement->profile_id = $profile->id;
            $lockedEntitlement->save();

            // Seed initial blocks from profession preset if selected
            if ($professionTypeId) {
                $profession = ProfessionType::find($professionTypeId);
                if ($profession && is_array($profession->default_blocks)) {
                    $orderIndex = 0;
                    foreach ($profession->default_blocks as $defaultBlock) {
                        $type = ProfileBlockType::tryFrom($defaultBlock['type'] ?? '');
                        if ($type) {
                            ProfileBlock::create([
                                'profile_id' => $profile->id,
                                'type' => $type,
                                'sort_order' => $orderIndex++,
                                'is_enabled' => true,
                                'settings' => array_merge($defaultBlock['config'] ?? [], ['title' => $defaultBlock['title'] ?? $type->label()]),
                                'content' => $defaultBlock['content'] ?? [],
                            ]);
                        }
                    }
                }
            }

            // Log audit
            $this->auditLogService->log(
                'profile.created',
                $profile,
                null,
                ['profile_id' => $profile->id, 'slug' => $profile->slug, 'entitlement_id' => $lockedEntitlement->id],
                'Created initial draft profile bound to card entitlement',
                $user
            );

            return $profile;
        });
    }
}
