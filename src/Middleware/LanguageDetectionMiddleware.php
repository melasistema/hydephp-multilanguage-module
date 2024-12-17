<?php

declare(strict_types=1);

namespace Melasistema\HydeMultilanguageModule\Middleware;

use Closure;

class LanguageDetectionMiddleware
{
    public function handle($request, Closure $next)
    {
        $language = $request->segment(1);

        if (in_array($language, config('hyde-multilanguage.supported_languages', ['en']))) {
            app()->setLocale($language);
        } else {
            // Default to English or redirect to default language
            return redirect('en/' . $request->path());
        }

        return $next($request);
    }
}