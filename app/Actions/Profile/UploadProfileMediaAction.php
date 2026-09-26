<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Enums\ProfileStatus;
use App\Models\Profile;
use App\Models\ProfileMedia;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UploadProfileMediaAction
{
    /**
     * Allowed MIME types for profile image uploads. Raw SVG is strictly rejected for XSS protection.
     */
    protected const ALLOWED_IMAGE_MIMES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    /**
     * Allowed MIME types for document attachments.
     */
    protected const ALLOWED_DOC_MIMES = [
        'application/pdf',
    ];

    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Validate and upload media asset for a profile.
     *
     * @param User $user
     * @param Profile $profile
     * @param UploadedFile $file
     * @param string $collection
     * @return ProfileMedia
     * @throws ValidationException
     */
    public function execute(
        User $user,
        Profile $profile,
        UploadedFile $file,
        string $collection = 'media'
    ): ProfileMedia {
        if ($profile->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'profile' => [__('You do not own this profile.')],
            ]);
        }

        if ($profile->status === ProfileStatus::Suspended) {
            throw ValidationException::withMessages([
                'profile' => [__('This profile is suspended and media cannot be uploaded.')],
            ]);
        }

        $mimeType = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());
        $isDoc = in_array($collection, ['document', 'attachment'], true);

        // Reject SVG unconditionally
        if ($mimeType === 'image/svg+xml' || $extension === 'svg') {
            throw ValidationException::withMessages([
                'file' => [__('Raw SVG files are disallowed for security and XSS prevention.')],
            ]);
        }

        // Reject Executables unconditionally
        $blockedExtensions = ['exe', 'bat', 'sh', 'php', 'phtml', 'phar', 'py', 'pl', 'js', 'jar', 'vbs', 'scr'];
        if (in_array($extension, $blockedExtensions, true)) {
            throw ValidationException::withMessages([
                'file' => [__('Executable files are strictly forbidden.')],
            ]);
        }

        $width = null;
        $height = null;

        if ($isDoc) {
            // PDF Document validation
            if (! in_array($mimeType, self::ALLOWED_DOC_MIMES, true) || $extension !== 'pdf') {
                throw ValidationException::withMessages([
                    'file' => [__('Invalid document type. Only PDF documents are permitted.')],
                ]);
            }

            // Max 10MB for documents
            if ($file->getSize() > (10 * 1024 * 1024)) {
                throw ValidationException::withMessages([
                    'file' => [__('Document size exceeds the 10MB maximum limit.')],
                ]);
            }

            // Verify PDF header magic bytes (%PDF-)
            $realPath = $file->getRealPath();
            $header = @file_get_contents($realPath, false, null, 0, 5);
            if ($header !== '%PDF-') {
                throw ValidationException::withMessages([
                    'file' => [__('Corrupted or invalid PDF file.')],
                ]);
            }
        } else {
            // Image validation
            if (! in_array($mimeType, self::ALLOWED_IMAGE_MIMES, true)) {
                throw ValidationException::withMessages([
                    'file' => [__('Invalid file type. Allowed formats: JPEG, PNG, WEBP. SVG is disallowed for security reasons.')],
                ]);
            }

            // Max 5MB for images
            if ($file->getSize() > (5 * 1024 * 1024)) {
                throw ValidationException::withMessages([
                    'file' => [__('File size exceeds the 5MB maximum limit.')],
                ]);
            }

            // Verify image dimensions & detect spoofed binary/script files
            $realPath = $file->getRealPath();
            $imageInfo = @getimagesize($realPath);
            if ($imageInfo === false) {
                throw ValidationException::withMessages([
                    'file' => [__('Corrupted or invalid image file.')],
                ]);
            }

            $width = $imageInfo[0];
            $height = $imageInfo[1];
        }

        // Store file securely with randomized safe filename
        $safeExtension = $isDoc ? 'pdf' : ($file->guessExtension() ?? 'bin');
        $fileName = bin2hex(random_bytes(16)) . '.' . $safeExtension;
        $disk = 'public';
        $directory = "profiles/{$profile->id}/{$collection}";
        $storedPath = $file->storeAs($directory, $fileName, $disk);

        $media = ProfileMedia::create([
            'profile_id' => $profile->id,
            'collection' => $collection,
            'file_path' => $storedPath,
            'disk' => $disk,
            'mime_type' => $mimeType,
            'file_size' => $file->getSize(),
            'width' => $width,
            'height' => $height,
        ]);

        $this->auditLogService->log(
            'profile.media_uploaded',
            $media,
            null,
            ['id' => $media->id, 'collection' => $collection, 'path' => $storedPath],
            "Uploaded media for profile {$profile->id}",
            $user
        );

        return $media;
    }
}
