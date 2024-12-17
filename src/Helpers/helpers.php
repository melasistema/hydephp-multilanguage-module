<?php

declare(strict_types=1);

if (! function_exists('translate')) {
    /**
     * Simple translation function.
     *
     * @param  string  $key
     * @param  string  $locale
     * @return string
     */
    function translate(string $key, string $locale = 'en'): string
    {
        $path = resource_path("lang/{$locale}/messages.php");

        // If the translation file exists, include it and return the translation.
        $translations = file_exists($path) ? include $path : [];
        return $translations[$key] ?? $key;  // Return the translation or the key if not found
    }
}
