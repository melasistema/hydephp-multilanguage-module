<?php

declare(strict_types=1);

namespace Melasistema\HydeMultilanguageModule\Pages;

use Hyde\Pages\BladePage;

/**
 * Multilingual Blade Page.
 *
 * Extends the HydePHP BladePage class to add support for multilingual features.
 */
class MultilingualBladePage extends BladePage
{
    protected string $locale;

    public function __construct(string $identifier, string $locale = 'en', array $matter = [])
    {
        $this->locale = $locale;

        // Ensure the identifier is correctly handled for multilingual pages
        if ($locale !== config('hyde-multilanguage.default_language', 'en')) {
            // Ensure we don't have a nested locale like `de/it/index`
            $identifierParts = explode('/', $identifier);

            // Only prepend the locale if the first part isn't a locale
            if (count($identifierParts) === 1 || $identifierParts[0] !== $locale) {
                $identifier = "{$locale}/{$identifier}";
            }
        }

        parent::__construct($identifier, $matter);
    }

    /**
     * Override the route key generation to handle languages correctly.
     */
    public function getRouteKey(): string
    {
        // If the page is in the default language, use the normal route key
        if ($this->locale === config('hyde-multilanguage.default_language', 'en')) {
            return $this->routeKey;
        }

        // Avoid prepending the locale when it's already included in the route key
        if (strpos($this->routeKey, "{$this->locale}/") === 0) {
            return $this->routeKey; // Already prefixed with the locale
        }

        // Prepend the locale to the route key only once
        return "{$this->locale}/{$this->routeKey}";
    }

    /**
     * Override the output path to include the language directory if needed.
     */
    public function getOutputPath(): string
    {
        // If the page is in the default language, use the normal output path
        if ($this->locale === config('hyde-multilanguage.default_language', 'en')) {
            return parent::getOutputPath();
        }

        // Ensure the locale is only prepended once to the output path
        $outputPath = parent::getOutputPath();
        if (strpos($outputPath, "{$this->locale}/") === 0) {
            return $outputPath; // Already prefixed with the locale
        }

        return "{$this->locale}/{$outputPath}";
    }
}
