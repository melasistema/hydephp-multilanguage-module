<?php

declare(strict_types=1);

namespace Melasistema\HydeMultilanguageModule\Pages;

use Hyde\Facades\Config;
use Hyde\Foundation\Facades\Routes;
use Hyde\Framework\Actions\GeneratesTableOfContents;
use Hyde\Support\Models\Route;
use Hyde\Pages\DocumentationPage;

use function trim;
use function sprintf;
use function Hyde\unslash;
use function basename;

/**
 * Multilingual Page class for documentation pages.
 *
 * Documentation pages are stored in the _docs directory and use the .md extension.
 * The Markdown will be compiled to HTML using the documentation page layout to the _site/docs/<locale>/ directory.
 *
 * @see https://hydephp.com/docs/1.x/documentation-pages
 */
class MultilingualDocumentationPage extends DocumentationPage
{
    public string $locale;

    /**
     * Constructor for the multilingual documentation page.
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
     * Get the localized output path for the documentation page.
     *
     * @return string
     */
    public function getLocalizedOutputPath(): string
    {
        $baseOutputPath = parent::getOutputPath();
        $defaultLocale = Config::get('hyde-multilanguage.default_language', 'en');

        return $this->locale === $defaultLocale
            ? $baseOutputPath
            : $this->locale . '/' . $baseOutputPath;
    }

    /**
     * Get the route key for the documentation page.
     *
     * Adjusts the route key to include the locale prefix.
     *
     * @return string
     */
    public function getRouteKey(): string
    {
        $baseRouteKey = parent::getRouteKey();
        $defaultLocale = Config::get('hyde-multilanguage.default_language', 'en');

        return $this->locale === $defaultLocale
            ? $baseRouteKey
            : $this->locale . '/' . $baseRouteKey;
    }

    /**
     * Get the path where the compiled page will be saved.
     *
     * Adjusts the output path to include the locale prefix.
     *
     * @return string
     */
    public function getOutputPath(): string
    {
        return $this->getLocalizedOutputPath();
    }

    /**
     * Get the online source path, adjusted for locale.
     *
     * @return string|false
     */
    public function getOnlineSourcePath(): string|false
    {
        $sourcePath = parent::getOnlineSourcePath();

        return $sourcePath ? str_replace('/docs/', "/docs/{$this->locale}/", $sourcePath) : false;
    }
}
