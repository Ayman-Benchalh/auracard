<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Enums\ProfileBlockType;
use App\Enums\ProfileStatus;
use App\Models\Profile;
use App\Models\ProfileBlock;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\Profile\BlockValidationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaveProfileBlockAction
{
    public function __construct(
        protected BlockValidationService $blockValidationService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Create or update a profile block in draft.
     *
     * @param User $user
     * @param Profile $profile
     * @param array<string, mixed> $data
     * @param ProfileBlock|null $existingBlock
     * @return ProfileBlock
     * @throws ValidationException
     */
    public function execute(
        User $user,
        Profile $profile,
        array $data,
        ?ProfileBlock $existingBlock = null
    ): ProfileBlock {
        return DB::transaction(function () use ($user, $profile, $data, $existingBlock) {
            if ($profile->user_id !== $user->id) {
                throw ValidationException::withMessages([
                    'profile' => [__('You do not own this profile.')],
                ]);
            }

            if ($profile->status === ProfileStatus::Suspended) {
                throw ValidationException::withMessages([
                    'profile' => [__('This profile is suspended and blocks cannot be modified.')],
                ]);
            }

            if ($existingBlock && $existingBlock->profile_id !== $profile->id) {
                throw ValidationException::withMessages([
                    'block' => [__('This block does not belong to this profile.')],
                ]);
            }

            // Validate using BlockValidationService
            $validated = $this->blockValidationService->validateBlock($data);

            $type = ProfileBlockType::from($validated['type']);
            $title = $validated['title'] ?? $type->label();
            $isVisible = $validated['is_visible'] ?? true;
            $config = $validated['config'] ?? [];
            $content = $validated['content'] ?? [];

            $settings = $config;
            $settings['title'] = $title;

            if ($existingBlock) {
                $before = $existingBlock->toArray();

                $existingBlock->type = $type;
                $existingBlock->sort_order = $existingBlock->sort_order;
                $existingBlock->is_enabled = $isVisible;
                $existingBlock->settings = $settings;
                $existingBlock->content = $content;
                $existingBlock->save();

                $this->auditLogService->log(
                    'profile.block_updated',
                    $existingBlock,
                    $before,
                    $existingBlock->toArray(),
                    "Updated block {$existingBlock->id}",
                    $user
                );

                return $existingBlock;
            }

            // Determine next sort_order
            $maxOrder = (int) $profile->blocks()->max('sort_order');
            $nextOrder = $profile->blocks()->exists() ? $maxOrder + 1 : 0;

            $block = ProfileBlock::create([
                'profile_id' => $profile->id,
                'type' => $type,
                'sort_order' => $nextOrder,
                'is_enabled' => $isVisible,
                'settings' => $settings,
                'content' => $content,
            ]);

            $this->auditLogService->log(
                'profile.block_created',
                $block,
                null,
                $block->toArray(),
                "Created block {$block->id} of type {$type->value}",
                $user
            );

            return $block;
        });
    }
}
