<?php

declare(strict_types=1);

namespace Melasistema\HydeMultilanguageModule\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        $locale = $request->segment(1); // Extract language code from URL
        $supportedLanguages = config('hyde-multilanguage.languages.supported');

        if (in_array($locale, $supportedLanguages)) {
            App::setLocale($locale); // Set the app's locale
        } else {
            $locale = config('hyde-multilanguage.languages.default');
        }

        return $next($request);
    }
}
