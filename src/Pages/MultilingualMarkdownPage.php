<?php

declare(strict_types=1);

namespace Melasistema\HydeMultilanguageModule\Pages;

use Hyde\Pages\MarkdownPage;

/**
 * Multilingual Markdown Page.
 *
 * Extends the MarkdownPage class to add support for multilingual features.
 */
class MultilingualMarkdownPage extends MarkdownPage
{
    public string $locale;

    /**
     * Constructor to initialize the Multilingual Markdown Page.
     *
     * @param string $identifier The file identifier (e.g., 'about').
     * @param string $locale The locale for the page (e.g., 'en', 'es').
     */
    public function __construct(string $identifier = '', string $locale = 'en')
    {
        $this->locale = $locale;
        parent::__construct($identifier);
    }

    /**
     * Get the localized identifier for the Markdown file.
     *
     * @return string
     */
    public function getLocalizedIdentifier(): string
    {
        // Example: "about" becomes "en/about" for localized versions.
        return $this->locale . '/' . $this->identifier;
    }

    /**
     * Get the output path for the localized Markdown page.
     *
     * @return string
     */
    public function getLocalizedOutputPath(): string
    {
        $baseOutputPath = parent::getOutputPath();

        // Add locale as a prefix, except for the default locale.
        $defaultLocale = config('hyde-multilanguage.default_language', 'en');

        return $this->locale === $defaultLocale
            ? $baseOutputPath
            : $this->locale . '/' . $baseOutputPath;
    }

    /**
     * Compile the Markdown page with localized content.
     *
     * @return string
     */
    public function compile(): string
    {
        $content = parent::compile();

        // Optionally prepend or append localization information
        return "<!-- Locale: {$this->locale} -->\n" . $content;
    }
}
