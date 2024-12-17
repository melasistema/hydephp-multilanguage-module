<?php

declare(strict_types=1);

namespace Melasistema\HydeMultilanguageModule\Pages;

use Hyde\Markdown\Models\FrontMatter;
use Hyde\Pages\BladePage;
use Illuminate\Support\Facades\View;

/**
 * Multilingual Blade Page.
 *
 * Extends the HydePHP BladePage class to add support for multilingual features.
 */
class MultilingualBladePage extends BladePage
{
    public string $locale;

    /**
     * Constructor to initialize the Multilingual Blade Page.
     *
     * @param string $identifier The identifier, which also serves as the view key.
     * @param string $locale The locale for the page (e.g., 'en', 'it').
     * @param FrontMatter|array $matter Optional front matter for the page.
     */
    public function __construct(string $identifier = '', string $locale = 'en', FrontMatter|array $matter = [])
    {
        $this->locale = $locale;
        parent::__construct($identifier, $matter);
    }

    /**
     * Get the Blade view for this page with a locale prefix.
     *
     * @return string
     */
    public function getBladeView(): string
    {
        $viewName = $this->locale . '.' . $this->identifier;

        // Ensure that the full path like '_pages/' is not included in the identifier
        return $this->identifier;  // This now just returns the identifier (e.g., 'it.about')
    }


    /**
     * Compile the Blade page with localized content.
     *
     * @return string
     */
    public function compile(): string
    {
        return View::make($this->getBladeView(), [
            'locale' => $this->locale,
            'page' => $this,
        ])->render();
    }

    /**
     * Get the localized URL path for this page.
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
}
