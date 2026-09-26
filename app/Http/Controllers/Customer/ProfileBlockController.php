<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Actions\Profile\DeleteProfileBlockAction;
use App\Actions\Profile\ReorderProfileBlocksAction;
use App\Actions\Profile\SaveProfileBlockAction;
use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\ProfileBlock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProfileBlockController extends Controller
{
    public function __construct(
        protected SaveProfileBlockAction $saveBlockAction,
        protected ReorderProfileBlocksAction $reorderBlocksAction,
        protected DeleteProfileBlockAction $deleteBlockAction
    ) {}

    /**
     * Store or update a block in profile draft.
     */
    public function store(Request $request, Profile $profile): JsonResponse|RedirectResponse
    {
        Gate::authorize('update', $profile);

        $user = $request->user();

        $data = $request->validate([
            'id' => ['nullable', 'string', 'exists:profile_blocks,id'],
            'type' => ['required', 'string'],
            'title' => ['nullable', 'string', 'max:255'],
            'is_visible' => ['sometimes', 'boolean'],
            'config' => ['nullable', 'array'],
            'content' => ['nullable', 'array'],
        ]);

        $existingBlock = ! empty($data['id'])
            ? ProfileBlock::where('id', $data['id'])->where('profile_id', $profile->id)->first()
            : null;

        $block = $this->saveBlockAction->execute($user, $profile, $data, $existingBlock);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'block' => [
                    'id' => $block->id,
                    'type' => $block->type->value,
                    'title' => $block->title,
                    'order_index' => $block->order_index,
                    'is_visible' => $block->is_visible,
                    'config' => $block->config,
                    'content' => $block->content,
                ],
            ]);
        }

        return back()->with('success', __('Block saved successfully.'));
    }

    /**
     * Reorder blocks inside draft.
     */
    public function reorder(Request $request, Profile $profile): JsonResponse|RedirectResponse
    {
        Gate::authorize('update', $profile);

        $user = $request->user();

        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['required', 'string', 'exists:profile_blocks,id'],
        ]);

        $this->reorderBlocksAction->execute($user, $profile, $validated['order']);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', __('Blocks reordered successfully.'));
    }

    /**
     * Delete a block from draft.
     */
    public function destroy(Request $request, Profile $profile, ProfileBlock $block): JsonResponse|RedirectResponse
    {
        Gate::authorize('update', $profile);

        if ($block->profile_id !== $profile->id) {
            abort(404, __('Block does not belong to this profile.'));
        }

        $user = $request->user();

        $this->deleteBlockAction->execute($user, $profile, $block);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', __('Block deleted successfully.'));
    }
}
