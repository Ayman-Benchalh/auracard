<?php

namespace App\Services\Profile;

use App\Models\Profile;
use Illuminate\Support\Str;

class ProfileSlugService
{
    /**
     * Reserved slug blacklist.
     */
    protected array $reservedSlugs = [
        'admin',
        'login',
        'register',
        'dashboard',
        'api',
        'support',
        'pricing',
        'cards',
        'c',
        'p',
        'profile',
        'profiles',
        'terms',
        'privacy',
        'home',
        'app',
        'auth',
        'filament',
        'livewire',
        'storage',
        'up',
        'sanctum',
        'vcard',
        'qr',
        'account',
        'locale',
        'settings',
        'billing',
        'help',
        'status',
    ];

    /**
     * Check if a given slug is reserved.
     */
    public function isReserved(string $slug): bool
    {
        return in_array(strtolower(trim($slug)), $this->reservedSlugs, true);
    }

    /**
     * Generate a unique, normalized slug from a display name.
     */
    public function generateUniqueSlug(string $name, ?string $ignoreProfileId = null): string
    {
        $base = Str::slug($name);

        if (empty($base)) {
            $base = 'aura-' . strtolower(Str::random(6));
        }

        if ($this->isReserved($base)) {
            $base .= '-profile';
        }

        $slug = $base;
        $counter = 1;

        while ($this->slugExists($slug, $ignoreProfileId)) {
            $counter++;
            $slug = "{$base}-{$counter}";
        }

        return $slug;
    }

    /**
     * Check whether a slug already exists in database.
     */
    protected function slugExists(string $slug, ?string $ignoreProfileId = null): bool
    {
        $query = Profile::where('slug', $slug);

        if ($ignoreProfileId) {
            $query->where('id', '!=', $ignoreProfileId);
        }

        return $query->exists() || $this->isReserved($slug);
    }
}
