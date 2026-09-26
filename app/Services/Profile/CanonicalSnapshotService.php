<?php

namespace App\Services\Profile;

use App\Models\Profile;

class CanonicalSnapshotService
{
    /**
     * Build the canonical snapshot array and compute its SHA-256 content hash.
     *
     * @return array{content: array, design: array, hash: string, canonical_json: string}
     */
    public function makeSnapshot(Profile $profile): array
    {
        $profile->loadMissing(['template', 'theme', 'blocks' => fn ($q) => $q->orderBy('sort_order')]);

        // 1. Content snapshot
        $content = [
            'identity' => [
                'display_name' => trim((string) $profile->display_name),
                'professional_title' => $profile->professional_title ? trim((string) $profile->professional_title) : null,
                'company_name' => $profile->company_name ? trim((string) $profile->company_name) : null,
                'bio' => $profile->bio ? trim((string) $profile->bio) : null,
                'seo_title' => $profile->seo_title ? trim((string) $profile->seo_title) : null,
                'seo_description' => $profile->seo_description ? trim((string) $profile->seo_description) : null,
            ],
            'profession_key' => $profile->professionType?->key,
            'template_key' => $profile->template?->key ?? 'aura_minimal',
            'theme_key' => $profile->theme?->key ?? 'aura_indigo',
            'blocks' => $profile->blocks->map(function ($block) {
                return [
                    'id' => (string) $block->id,
                    'type' => $block->type->value,
                    'sort_order' => (int) $block->sort_order,
                    'is_enabled' => (bool) $block->is_enabled,
                    'content' => $this->recursiveKsort($block->content ?? []),
                    'settings' => $this->recursiveKsort($block->settings ?? []),
                ];
            })->values()->all(),
        ];

        // 2. Design snapshot (using draft tokens or theme defaults)
        $tokens = $profile->draft_design_tokens ?? $profile->theme?->default_tokens ?? [];
        if (is_array($tokens)) {
            unset($tokens['vcard_cache']);
        }
        $design = [
            'tokens' => $this->recursiveKsort($tokens),
        ];

        // 3. Normalized structure for stable hashing
        $normalized = [
            'content' => $this->recursiveKsort($content),
            'design' => $this->recursiveKsort($design),
        ];

        $canonicalJson = json_encode($normalized, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $hash = hash('sha256', $canonicalJson);

        return [
            'content' => $content,
            'design' => $design,
            'hash' => $hash,
            'canonical_json' => $canonicalJson,
        ];
    }

    /**
     * Build combined snapshot array for publishing.
     */
    public function buildSnapshot(Profile $profile, mixed $blocks = null): array
    {
        $snap = $this->makeSnapshot($profile);
        $result = $snap['content'];
        $tokens = $snap['design']['tokens'] ?? [];
        if (is_array($tokens)) {
            unset($tokens['vcard_cache']);
        }
        $result['design'] = $tokens;
        $result['display_name'] = $profile->display_name;
        $result['headline'] = $profile->headline;
        $result['bio'] = $profile->bio;
        $result['company'] = $profile->company;
        $result['job_title'] = $profile->job_title;
        $result['location'] = $profile->location;
        $result['avatar_url'] = $profile->avatar_url;
        $result['cover_url'] = $profile->cover_url;
        $result['primary_color'] = $profile->primary_color;
        $result['secondary_color'] = $profile->secondary_color;
        $result['font_family'] = $profile->font_family;
        $result['custom_css'] = $profile->custom_css;
        return $result;
    }

    /**
     * Compute deterministic SHA-256 hash of a snapshot.
     */
    public function calculateHash(array $snapshot): string
    {
        $sorted = $this->recursiveKsort($snapshot);
        $canonicalJson = json_encode($sorted, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return hash('sha256', $canonicalJson);
    }

    /**
     * Recursively sort array keys to guarantee identical JSON encoding.
     */
    public function recursiveKsort(mixed $data): mixed
    {
        if (! is_array($data)) {
            return $data;
        }

        // If list (sequential numeric keys 0..n), sort each element but preserve index order
        if (array_is_list($data)) {
            return array_map([$this, 'recursiveKsort'], $data);
        }

        ksort($data);

        foreach ($data as $key => $value) {
            $data[$key] = $this->recursiveKsort($value);
        }

        return $data;
    }
}
