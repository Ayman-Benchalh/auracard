<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Enums\ProfileStatus;
use App\Models\Profile;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SuspendProfileAdminAction
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Admin action to suspend or unsuspend a profile.
     *
     * @param User $admin
     * @param Profile $profile
     * @param bool $suspend
     * @param string|null $reason
     * @return Profile
     * @throws ValidationException
     */
    public function execute(User $admin, Profile $profile, bool $suspend, ?string $reason = null): Profile
    {
        return DB::transaction(function () use ($admin, $profile, $suspend, $reason) {
            $lockedProfile = Profile::where('id', $profile->id)
                ->lockForUpdate()
                ->firstOrFail();

            $beforeStatus = $lockedProfile->status;

            if ($suspend) {
                if ($lockedProfile->status === ProfileStatus::Suspended) {
                    return $lockedProfile;
                }
                $lockedProfile->status = ProfileStatus::Suspended;
            } else {
                if ($lockedProfile->status !== ProfileStatus::Suspended) {
                    return $lockedProfile;
                }
                // Restore to Published if it has a published version, otherwise Draft
                $lockedProfile->status = $lockedProfile->published_version_id !== null
                    ? ProfileStatus::Published
                    : ProfileStatus::Draft;
            }

            $lockedProfile->save();

            $this->auditLogService->log(
                $suspend ? 'admin.profile_suspended' : 'admin.profile_unsuspended',
                $lockedProfile,
                ['status' => $beforeStatus->value],
                ['status' => $lockedProfile->status->value],
                $reason ?? ($suspend ? 'Profile suspended by administrator' : 'Profile unsuspended by administrator'),
                $admin
            );

            return $lockedProfile;
        });
    }
}
