<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supported = config('auracard.locales.supported', ['ar', 'fr', 'en']);
        $default = config('auracard.locales.default', 'fr');

        $locale = $default;

        if ($request->user() && in_array($request->user()->locale, $supported, true)) {
            $locale = $request->user()->locale;
        } elseif ($request->session()->has('locale') && in_array($request->session()->get('locale'), $supported, true)) {
            $locale = $request->session()->get('locale');
        } elseif ($request->hasHeader('Accept-Language')) {
            $preferred = $request->getPreferredLanguage($supported);
            if ($preferred && in_array($preferred, $supported, true)) {
                $locale = $preferred;
            }
        }

        App::setLocale($locale);

        return $next($request);
    }
}
