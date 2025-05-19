<?php

declare(strict_types=1);

use Illuminate\Contracts\Container\BindingResolutionException;
use Melasistema\HydeMultilanguageModule\Services\TranslationService;

if (! function_exists('translate')) {
    /**
     * Translate the given message key for the given locale using the custom TranslationService.
     *
     * @param string $key
     * @param string|null $locale
     * @return string
     */
    function translate(string $key, string $locale = null): string
    {
        // Determine the locale to use.
        $currentLocale = $locale ?? config('hyde-multilanguage.default_language', 'en');

        // Ensure a locale is determined
        if (empty($currentLocale)) {
            // This case should ideally not happen if config default is set
            $currentLocale = 'en';
            \Illuminate\Support\Facades\Log::warning("Translate helper: Locale not determined, falling back to 'en' for key '{$key}'.");
        }

        // Attempt to resolve the TranslationService from the container
        // and get the translation using the determined locale.
        try {
            $translationService = app(TranslationService::class);
            // The service should handle loading the translations for $currentLocale
            // if they are not already loaded.
            $translationService->loadTranslations($currentLocale); // Ensure translations for locale are loaded
            return $translationService->get($key, $currentLocale); // Gets translation from loaded array
        } catch (BindingResolutionException $e) {
            // This catch is for TranslationService binding failing,
            // which is unlikely now that it's registered in ServiceProvider.
            \Illuminate\Support\Facades\Log::error("Translate helper: TranslationService not available for key '{$key}': " . $e->getMessage());
            return $key; // Fallback to key if service fails to resolve
        } catch (\Throwable $e) {
            // Catch any other errors during translation fetching (e.g., file errors in loadTranslations)
            \Illuminate\Support\Facades\Log::error("Translate helper: Error fetching translation for key '{$key}' and locale '{$currentLocale}': " . $e->getMessage());
            return $key; // Fallback to the key on error
        }
    }
}