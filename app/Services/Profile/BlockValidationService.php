<?php

declare(strict_types=1);

namespace App\Services\Profile;

use App\Enums\ProfileBlockType;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BlockValidationService
{
    /**
     * Allowed URL schemes across all block configurations.
     */
    protected const ALLOWED_SCHEMES = ['http', 'https', 'mailto', 'tel', 'sms'];

    /**
     * Validate block payload (type, title, is_visible, config, content).
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     * @throws ValidationException
     */
    public function validateBlock(array $data): array
    {
        $validator = Validator::make($data, [
            'type' => ['required', 'string'],
            'title' => ['nullable', 'string', 'max:255'],
            'is_visible' => ['sometimes', 'boolean'],
            'config' => ['nullable', 'array'],
            'content' => ['nullable', 'array'],
        ]);

        $validated = $validator->validate();

        $typeEnum = ProfileBlockType::tryFrom($validated['type']);
        if (! $typeEnum) {
            throw ValidationException::withMessages([
                'type' => ["Unknown or unsupported block type: {$validated['type']}"],
            ]);
        }

        $config = $validated['config'] ?? [];
        $content = $validated['content'] ?? [];

        // Validate specific block content structure
        $validatedContent = $this->validateBlockTypeContent($typeEnum, $content);
        $validatedConfig = $this->validateBlockTypeConfig($typeEnum, $config);

        // Sanitize any URLs in config or content recursively
        $this->ensureSafeUrls($validatedContent);
        $this->ensureSafeUrls($validatedConfig);

        $validated['content'] = $validatedContent;
        $validated['config'] = $validatedConfig;

        return $validated;
    }

    /**
     * Validate content per block type.
     *
     * @param ProfileBlockType $type
     * @param array<string, mixed> $content
     * @return array<string, mixed>
     */
    protected function validateBlockTypeContent(ProfileBlockType $type, array $content): array
    {
        $rules = match ($type) {
            ProfileBlockType::CONTACT_ACTIONS => [
                'items' => ['required', 'array', 'min:1', 'max:10'],
                'items.*.type' => ['required', 'string', 'in:phone,email,whatsapp,telegram,sms,website,address'],
                'items.*.label' => ['required', 'string', 'max:100'],
                'items.*.value' => ['required', 'string', 'max:255'],
                'items.*.is_primary' => ['sometimes', 'boolean'],
            ],
            ProfileBlockType::SOCIAL_LINKS => [
                'items' => ['required', 'array', 'min:1', 'max:20'],
                'items.*.platform' => ['required', 'string', 'max:50'],
                'items.*.url' => ['required', 'string', 'max:500'],
                'items.*.username' => ['nullable', 'string', 'max:100'],
            ],
            ProfileBlockType::BIO => [
                'headline' => ['nullable', 'string', 'max:255'],
                'bio' => ['required', 'string', 'max:3000'],
                'image_url' => ['nullable', 'string', 'max:500'],
            ],
            ProfileBlockType::SERVICES => [
                'items' => ['required', 'array', 'min:1', 'max:20'],
                'items.*.name' => ['required', 'string', 'max:200'],
                'items.*.description' => ['nullable', 'string', 'max:1000'],
                'items.*.price' => ['nullable', 'string', 'max:50'],
                'items.*.icon' => ['nullable', 'string', 'max:100'],
                'items.*.action_url' => ['nullable', 'string', 'max:500'],
            ],
            ProfileBlockType::PORTFOLIO => [
                'items' => ['required', 'array', 'min:1', 'max:24'],
                'items.*.title' => ['required', 'string', 'max:200'],
                'items.*.description' => ['nullable', 'string', 'max:1000'],
                'items.*.image_url' => ['required', 'string', 'max:500'],
                'items.*.link_url' => ['nullable', 'string', 'max:500'],
            ],
            ProfileBlockType::PROJECTS => [
                'items' => ['required', 'array', 'min:1', 'max:25'],
                'items.*.title' => ['required', 'string', 'max:200'],
                'items.*.url' => ['required', 'string', 'max:500'],
                'items.*.subtitle' => ['nullable', 'string', 'max:200'],
                'items.*.icon' => ['nullable', 'string', 'max:100'],
                'items.*.is_highlighted' => ['sometimes', 'boolean'],
            ],
            ProfileBlockType::LOCATION => [
                'address' => ['required', 'string', 'max:300'],
                'latitude' => ['nullable', 'numeric', 'between:-90,90'],
                'longitude' => ['nullable', 'numeric', 'between:-180,180'],
                'embed_url' => ['nullable', 'string', 'max:1000'],
                'zoom' => ['nullable', 'integer', 'between:1,20'],
            ],
            ProfileBlockType::BUSINESS_HOURS => [
                'timezone' => ['nullable', 'string', 'max:50'],
                'days' => ['required', 'array'],
                'days.*.day' => ['required', 'string', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
                'days.*.open' => ['nullable', 'string', 'max:10'],
                'days.*.close' => ['nullable', 'string', 'max:10'],
                'days.*.is_closed' => ['required', 'boolean'],
            ],
            ProfileBlockType::TESTIMONIALS => [
                'items' => ['required', 'array', 'min:1', 'max:15'],
                'items.*.author' => ['required', 'string', 'max:150'],
                'items.*.role' => ['nullable', 'string', 'max:150'],
                'items.*.company' => ['nullable', 'string', 'max:150'],
                'items.*.content' => ['required', 'string', 'max:1500'],
                'items.*.avatar_url' => ['nullable', 'string', 'max:500'],
                'items.*.rating' => ['nullable', 'integer', 'between:1,5'],
            ],
            ProfileBlockType::BOOKING => [
                'provider' => ['required', 'string', 'in:calendly,cal_com,acuity,custom'],
                'booking_url' => ['required', 'string', 'max:500'],
                'button_text' => ['nullable', 'string', 'max:100'],
                'instructions' => ['nullable', 'string', 'max:500'],
            ],
            ProfileBlockType::LEAD_FORM => [
                'headline' => ['required', 'string', 'max:200'],
                'description' => ['nullable', 'string', 'max:500'],
                'fields' => ['required', 'array', 'min:1', 'max:5'],
                'fields.*.name' => ['required', 'string', 'max:50'],
                'fields.*.label' => ['required', 'string', 'max:100'],
                'fields.*.type' => ['required', 'string', 'in:text,email,phone,textarea'],
                'fields.*.required' => ['required', 'boolean'],
                'submit_label' => ['nullable', 'string', 'max:100'],
                'notification_email' => ['nullable', 'email', 'max:255'],
            ],
            ProfileBlockType::CUSTOM_CTA => [
                'html' => ['required', 'string', 'max:10000'],
            ],
            ProfileBlockType::DOCUMENTS => [
                'items' => ['required', 'array', 'min:1', 'max:10'],
                'items.*.title' => ['required', 'string', 'max:200'],
                'items.*.file_url' => ['required', 'string', 'max:500'],
                'items.*.file_size_formatted' => ['nullable', 'string', 'max:30'],
                'items.*.file_type' => ['nullable', 'string', 'max:30'],
            ],
            ProfileBlockType::GALLERY => [
                'items' => ['required', 'array', 'min:1', 'max:20'],
                'items.*.image_url' => ['required', 'string', 'max:500'],
                'items.*.caption' => ['nullable', 'string', 'max:200'],
            ],
            ProfileBlockType::CERTIFICATIONS => [
                'items' => ['required', 'array', 'min:1', 'max:15'],
                'items.*.title' => ['required', 'string', 'max:150'],
                'items.*.issuer' => ['nullable', 'string', 'max:150'],
                'items.*.badge_url' => ['required', 'string', 'max:500'],
                'items.*.verify_url' => ['nullable', 'string', 'max:500'],
            ],
            ProfileBlockType::PRODUCTS => [
                'items' => ['required', 'array', 'min:1', 'max:6'],
                'items.*.title' => ['required', 'string', 'max:150'],
                'items.*.price' => ['required', 'string', 'max:50'],
                'items.*.period' => ['nullable', 'string', 'max:50'],
                'items.*.features' => ['required', 'array'],
                'items.*.features.*' => ['required', 'string', 'max:200'],
                'items.*.is_highlighted' => ['sometimes', 'boolean'],
                'items.*.cta_text' => ['nullable', 'string', 'max:100'],
                'items.*.cta_url' => ['nullable', 'string', 'max:500'],
            ],
            ProfileBlockType::SKILLS => [
                'items' => ['required', 'array', 'min:1', 'max:15'],
                'items.*.name' => ['required', 'string', 'max:100'],
                'items.*.level' => ['nullable', 'string', 'max:50'],
            ],
            ProfileBlockType::HEADER => [
                'image_url' => ['required', 'string', 'max:500'],
                'headline' => ['nullable', 'string', 'max:200'],
                'subtitle' => ['nullable', 'string', 'max:300'],
                'link_url' => ['nullable', 'string', 'max:500'],
            ],
            ProfileBlockType::VCARD => [
                'label' => ['nullable', 'string', 'max:100'],
                'include_qr' => ['sometimes', 'boolean'],
            ],
        };

        $val = Validator::make($content, $rules);
        $clean = $val->validate();

        if ($type === ProfileBlockType::CUSTOM_CTA && isset($clean['html'])) {
            $clean['html'] = $this->sanitizeHtml($clean['html']);
        }

        return $clean;
    }

    /**
     * Validate config array per block type.
     *
     * @param ProfileBlockType $type
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    protected function validateBlockTypeConfig(ProfileBlockType $type, array $config): array
    {
        return Validator::make($config, [
            'layout' => ['sometimes', 'string', 'in:default,compact,grid,carousel,cards'],
            'align' => ['sometimes', 'string', 'in:left,center,right'],
            'theme_accent' => ['sometimes', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'background_color' => ['sometimes', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'text_color' => ['sometimes', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'show_border' => ['sometimes', 'boolean'],
            'border_radius' => ['sometimes', 'string', 'in:none,sm,md,lg,xl,full'],
        ])->validate();
    }

    /**
     * Recursively scan arrays for URL strings and ensure no javascript: or data: injection.
     *
     * @param array<mixed> $data
     * @return void
     * @throws ValidationException
     */
    protected function ensureSafeUrls(array &$data): void
    {
        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                $this->ensureSafeUrls($value);
            } elseif (is_string($value)) {
                $trimmed = trim($value);
                if ($trimmed === '') {
                    continue;
                }

                $isUrlKey = str_contains($key, 'url') || str_contains($key, 'link');
                $hasSchemePrefix = (bool) preg_match('/^[a-zA-Z][a-zA-Z0-9+.-]*:/', $trimmed);

                if ($isUrlKey || $hasSchemePrefix) {
                    // Strictly reject dangerous executable URI schemes
                    if (preg_match('/^(javascript|data|vbscript):/i', $trimmed)) {
                        throw ValidationException::withMessages([
                            $key => ['Invalid or unsafe URL format provided.'],
                        ]);
                    }

                    $scheme = parse_url($trimmed, PHP_URL_SCHEME);
                    if ($scheme !== null) {
                        $schemeLower = strtolower($scheme);
                        if (! in_array($schemeLower, self::ALLOWED_SCHEMES, true)) {
                            throw ValidationException::withMessages([
                                $key => ["The URL scheme '{$scheme}' is not allowed for security."],
                            ]);
                        }
                    }
                }
            }
        }
    }

    /**
     * Sanitization for HTML content to prevent stored XSS.
     */
    public function sanitizeHtml(string $html): string
    {
        // 1. Remove dangerous executable/embed elements and their contents
        $html = preg_replace('/<(script|object|embed|applet|iframe|meta|link|style|form|input)[^>]*>.*?<\/\1>/is', '', $html) ?? '';
        $html = preg_replace('/<(script|object|embed|applet|iframe|meta|link|style|form|input)[^>]*>/is', '', $html) ?? '';

        // 2. Remove all inline event handlers (onerror, onload, onclick, onmouseover, etc.)
        $html = preg_replace('/\s+on[a-zA-Z]+\s*=\s*(["\']).*?\1/is', '', $html) ?? '';
        $html = preg_replace('/\s+on[a-zA-Z]+\s*=\s*[^>\s]+/is', '', $html) ?? '';

        // 3. Defang dangerous URI schemes in remaining tags
        $html = preg_replace('/(javascript|data|vbscript):/i', 'unsafe:', $html) ?? '';

        return trim($html);
    }
}
