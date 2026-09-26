<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Actions\Profile\UploadProfileMediaAction;
use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\ProfileMedia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ProfileMediaController extends Controller
{
    public function __construct(
        protected UploadProfileMediaAction $uploadMediaAction
    ) {}

    /**
     * Upload an image or document asset for a profile.
     */
    public function store(Request $request, Profile $profile): JsonResponse
    {
        Gate::authorize('uploadMedia', $profile);

        $collection = $request->input('collection', 'media');
        $isDoc = in_array($collection, ['document', 'attachment'], true);

        $rules = $isDoc
            ? ['required', 'file', 'mimes:pdf', 'max:10240']
            : ['required', 'file', 'mimes:jpeg,jpg,png,webp', 'max:5120'];

        $request->validate([
            'file' => $rules,
            'collection' => ['nullable', 'string', 'in:avatar,cover,media,portfolio,document,attachment,gallery'],
        ]);

        $media = $this->uploadMediaAction->execute($request->user(), $profile, $request->file('file'), $collection);

        return response()->json([
            'success' => true,
            'media' => [
                'id' => $media->id,
                'url' => $media->url,
                'file_name' => $media->file_name,
                'mime_type' => $media->mime_type,
                'file_size' => $media->file_size,
                'dimensions' => $media->dimensions,
                'collection' => $media->collection,
            ],
        ]);
    }

    /**
     * Delete a media asset owned by this profile.
     */
    public function destroy(Request $request, Profile $profile, ProfileMedia $media): JsonResponse
    {
        Gate::authorize('deleteMedia', $profile);

        if ($media->profile_id !== $profile->id) {
            abort(404, __('Media does not belong to this profile.'));
        }

        if ($media->file_path && Storage::disk($media->disk ?? 'public')->exists($media->file_path)) {
            Storage::disk($media->disk ?? 'public')->delete($media->file_path);
        }

        $media->delete();

        return response()->json([
            'success' => true,
            'message' => __('Media deleted successfully.'),
        ]);
    }
}
