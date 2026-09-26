<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Switch language and store in session / user profile.
     */
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        $supported = config('auracard.locales.supported', ['ar', 'fr', 'en']);

        if (! in_array($locale, $supported, true)) {
            $locale = config('auracard.locales.default', 'fr');
        }

        session(['locale' => $locale]);

        if ($request->user()) {
            $request->user()->update(['locale' => $locale]);
        }

        return back();
    }
}
