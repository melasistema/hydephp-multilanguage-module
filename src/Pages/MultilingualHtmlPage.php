<?php

declare(strict_types=1);

namespace Melasistema\HydeMultilanguageModule\Pages;

use Hyde\Pages\HtmlPage;

/**
 * Multilingual Page class for HTML pages.
 *
 * Html pages are stored in the _pages directory and use the .html extension.
 * These pages will be copied exactly as they are to the _site/<locale>/ directory, enabling multilingual support.
 *
 * @see https://hydephp.com/docs/1.x/static-pages#bonus-creating-html-pages
 */
class MultilingualHtmlPage extends HtmlPage
{
    public string $locale;

    /**
     * Constructor for the multilingual HTML page.
     *
     * @param string $identifier The page identifier used to generate the route and filename.
     * @param string $locale The locale for the page (e.g., 'en', 'es').
     */
    public function __construct(string $identifier = '', string $locale = 'en')
    {
        $this->locale = $locale;
        parent::__construct($identifier);
    }

    /**
     * Get the localized output path for the page.
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
     * Get the contents of the HTML page.
     *
     * Adds a comment indicating the locale for easier debugging.
     *
     * @return string
     */
    public function contents(): string
    {
        $content = parent::contents();
        return "<!-- Locale: {$this->locale} -->\n" . $content;
    }

    /**
     * Compile the page content.
     *
     * @return string
     */
    public function compile(): string
    {
        return $this->contents();
    }

    /**
     * Override the output path to use the localized path.
     *
     * @return string
     */
    public function getOutputPath(): string
    {
        return $this->getLocalizedOutputPath();
    }
}
