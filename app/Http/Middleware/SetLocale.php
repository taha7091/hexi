<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get locale from various sources in order of priority
        $locale = $this->getLocale($request);

        // Set the application locale
        if ($locale && in_array($locale, config('app.available_locales', ['en']))) {
            App::setLocale($locale);
        }

        return $next($request);
    }

    /**
     * Get the locale from various sources
     */
    private function getLocale(Request $request): ?string
    {
        // 1. Check if locale is being set via request (for settings update)
        if ($request->has('language')) {
            return $request->input('language');
        }

        // 2. Check user's saved preference (if authenticated)
        if (Auth::check() && Auth::user()->language_preference) {
            return Auth::user()->language_preference;
        }

        // 3. Check session
        if (Session::has('language')) {
            return Session::get('language');
        }

        // 4. Check browser language
        $browserLocale = $request->getPreferredLanguage(config('app.available_locales', ['en']));
        if ($browserLocale) {
            return $browserLocale;
        }

        // 5. Default to app locale
        return config('app.locale', 'en');
    }
}
