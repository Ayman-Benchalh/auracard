<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $locale = app()->getLocale();
        $direction = config("auracard.locales.directions.{$locale}", 'ltr');

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'phone' => $request->user()->phone,
                    'account_status' => $request->user()->account_status->value,
                    'locale' => $request->user()->locale,
                    'email_verified_at' => $request->user()->email_verified_at?->toISOString(),
                    'roles' => $request->user()->roles->pluck('name')->toArray(),
                ] : null,
            ],
            'locale' => $locale,
            'direction' => $direction,
            'supportedLocales' => config('auracard.locales.supported', ['ar', 'fr', 'en']),
            'translations' => $this->getTranslations($locale),
            'brand' => [
                'name' => config('auracard.brand.name', 'AURA CARD'),
                'whatsapp' => config('auracard.brand.support_whatsapp'),
                'email' => config('auracard.brand.support_email'),
            ],
            'socialAuth' => [
                'google' => (bool) config('services.google.enabled', true),
                'facebook' => (bool) config('services.facebook.enabled', true),
                'instagram' => (bool) config('services.instagram.enabled', false),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'status' => fn () => $request->session()->get('status'),
                'info' => fn () => $request->session()->get('info'),
            ],
        ]);
    }

    protected function getTranslations(string $locale): array
    {
        $file = lang_path("{$locale}.json");
        if (file_exists($file)) {
            $content = file_get_contents($file);
            return json_decode($content, true) ?: [];
        }

        return [];
    }
}
