<?php

declare(strict_types=1);

namespace Melasistema\HydeMultilanguageModule\Pages;

use Hyde\Pages\InMemoryPage;

/**
 * Multilingual In-Memory Page.
 *
 * Extends the InMemoryPage class to add support for multilingual features.
 */
class MultilingualInMemoryPage extends InMemoryPage
{
    public string $locale;

    /**
     * Constructor for a multilingual in-memory page.
     *
     * @param string $identifier The page identifier, used to generate the route and filename.
     * @param string $locale The locale for the page (e.g., 'en', 'es').
     * @param array $matter Front matter metadata for the page.
     * @param string $contents The raw contents of the page.
     * @param string $view The Blade view name to use for rendering (optional).
     */
    public function __construct(string $identifier = '', string $locale = 'en', array $matter = [], string $contents = '', string $view = '')
    {
        $this->locale = $locale;
        parent::__construct($identifier, $matter, $contents, $view);
    }

    /**
     * Get the localized identifier for the in-memory page.
     *
     * @return string
     */
    public function getLocalizedIdentifier(): string
    {
        return $this->locale . '/' . $this->identifier;
    }

    /**
     * Get the output path for the localized in-memory page.
     *
     * @return string
     */
    public function getLocalizedOutputPath(): string
    {
        $baseOutputPath = parent::getOutputPath();

        $defaultLocale = config('hyde-multilanguage.default_language', 'en');

        return $this->locale === $defaultLocale
            ? $baseOutputPath
            : $this->locale . '/' . $baseOutputPath;
    }

    /**
     * Compile the page content, taking into account the locale.
     *
     * @return string
     */
    public function compile(): string
    {
        $content = parent::compile();

        return "<!-- Locale: {$this->locale} -->\n" . $content;
    }
}
