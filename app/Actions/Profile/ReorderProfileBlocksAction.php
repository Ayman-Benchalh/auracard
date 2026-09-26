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

class ReorderProfileBlocksAction
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Reorder blocks within a profile draft.
     *
     * @param User $user
     * @param Profile $profile
     * @param array<int, string> $orderedBlockIds
     * @return void
     * @throws ValidationException
     */
    public function execute(User $user, Profile $profile, array $orderedBlockIds): void
    {
        DB::transaction(function () use ($user, $profile, $orderedBlockIds) {
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

            // Verify all block IDs belong to this profile
            $existingBlocks = ProfileBlock::where('profile_id', $profile->id)
                ->whereIn('id', $orderedBlockIds)
                ->get()
                ->keyBy('id');

            foreach ($orderedBlockIds as $index => $blockId) {
                if (isset($existingBlocks[$blockId])) {
                    $block = $existingBlocks[$blockId];
                    if ($block->order_index !== $index) {
                        $block->order_index = $index;
                        $block->save();
                    }
                }
            }

            $this->auditLogService->log(
                'profile.blocks_reordered',
                $profile,
                null,
                ['order' => $orderedBlockIds],
                'Reordered profile blocks',
                $user
            );
        });
    }
}
