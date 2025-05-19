<?php

declare(strict_types=1);

namespace Melasistema\HydeMultilanguageModule\Middleware;

use Closure;
use Hyde\Facades\Config;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        $segment1 = $request->segment(1);
        // Use the correct config key for supported languages
        $supportedLanguages = Config::get('hyde-multilanguage.translations', ['en']);
        $defaultLocale = Config::get('hyde-multilanguage.default_language', 'en');

        // Check if the first segment of the URL is a supported language code
        if (in_array($segment1, $supportedLanguages)) {
            $locale = $segment1;
            App::setLocale($locale); // Set the application locale

            // $request->path() returns the path without a leading slash (e.g., 'it', 'it/about')
            if ($request->path() === $locale) {
                // It's a request for the locale root (e.g., /it).
                // Redirect to the index page for this locale.
                $targetPath = $locale . '/index.html';

                Log::info("Redirecting locale root /{$locale} to /{$targetPath}");

                // Return a redirect response
                return Redirect::to($targetPath);
            }

            // If the first segment is a supported locale but the path has more segments
            // (e.g., /it/about), the locale is set, and we proceed to standard routing.
            return $next($request);

        } else {
            // The first segment is not a supported locale, or there is no first segment (root /).
            // Set the default locale.
            $locale = $defaultLocale;
            App::setLocale($locale);

            // If the request is for the root path '/' and the default locale is handled at '/',
            // this block correctly sets the default locale and allows Hyde to route the default index page.
            return $next($request);
        }
    }
}
