<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Enums\ProfileStatus;
use App\Models\Profile;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\Profile\ProfileSlugService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateProfileDraftAction
{
    public function __construct(
        protected ProfileSlugService $slugService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Update draft attributes of a profile. Does NOT alter the public published profile snapshot.
     *
     * @param User $user
     * @param Profile $profile
     * @param array<string, mixed> $data
     * @return Profile
     * @throws ValidationException
     */
    public function execute(User $user, Profile $profile, array $data): Profile
    {
        return DB::transaction(function () use ($user, $profile, $data) {
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
                    'profile' => [__('This profile is suspended and cannot be edited.')],
                ]);
            }

            $before = $lockedProfile->only([
                'display_name', 'headline', 'bio', 'company', 'job_title',
                'location', 'template_id', 'theme_id', 'primary_color', 'secondary_color',
            ]);

            // If slug changed, validate and format it
            if (isset($data['slug']) && $data['slug'] !== $lockedProfile->slug) {
                $desiredSlug = trim((string) $data['slug']);
                if ($this->slugService->isReserved($desiredSlug)) {
                    throw ValidationException::withMessages([
                        'slug' => [__('This URL handle is reserved by the system.')],
                    ]);
                }
                $newSlug = $this->slugService->generateUniqueSlug($desiredSlug, $lockedProfile->id);
                $lockedProfile->slug = $newSlug;
            }

            // Fill allowed fields
            $fillable = [
                'display_name',
                'headline',
                'bio',
                'company',
                'job_title',
                'location',
                'avatar_url',
                'cover_url',
                'profession_type_id',
                'template_id',
                'theme_id',
                'primary_color',
                'secondary_color',
                'font_family',
                'custom_css',
            ];

            foreach ($fillable as $field) {
                if (array_key_exists($field, $data)) {
                    $lockedProfile->{$field} = $data[$field];
                }
            }

            $lockedProfile->save();

            $this->auditLogService->log(
                'profile.draft_updated',
                $lockedProfile,
                $before,
                $lockedProfile->only(array_keys($before)),
                'Updated profile draft attributes',
                $user
            );

            return $lockedProfile;
        });
    }
}
