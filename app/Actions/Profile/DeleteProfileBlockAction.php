<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Enums\ProfileStatus;
use App\Models\Profile;
use App\Models\ProfileBlock;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteProfileBlockAction
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Delete a block from a draft profile.
     *
     * @param User $user
     * @param Profile $profile
     * @param ProfileBlock $block
     * @return void
     * @throws ValidationException
     */
    public function execute(User $user, Profile $profile, ProfileBlock $block): void
    {
        DB::transaction(function () use ($user, $profile, $block) {
            if ($profile->user_id !== $user->id) {
                throw ValidationException::withMessages([
                    'profile' => [__('You do not own this profile.')],
                ]);
            }

            if ($profile->status === ProfileStatus::Suspended) {
                throw ValidationException::withMessages([
                    'profile' => [__('This profile is suspended.')],
                ]);
            }

            if ($block->profile_id !== $profile->id) {
                throw ValidationException::withMessages([
                    'block' => [__('This block does not belong to this profile.')],
                ]);
            }

            $before = $block->toArray();
            $block->delete();

            $this->auditLogService->log(
                'profile.block_deleted',
                $profile,
                $before,
                null,
                "Deleted block {$block->id} ({$block->type->value})",
                $user
            );
        });
    }
}
