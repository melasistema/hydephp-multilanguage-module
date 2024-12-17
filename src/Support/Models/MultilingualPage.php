<?php

declare(strict_types=1);

namespace Melasistema\HydeMultilanguageModule\Support\Models;

use Hyde\Pages\InMemoryPage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\App;

class MultilingualPage extends InMemoryPage
{
    protected string $language;

    /**
     * Constructor that sets the language and initializes the page.
     *
     * @param string $path
     * @param string $language
     */
    public function __construct(string $path, string $language)
    {
        $this->language = $language;
        parent::__construct($path);
    }

    /**
     * Compiles the page content by rendering the page with translations.
     *
     * @return string
     */
    public function compile(): string
    {
        // Get the translations for the current language
        $translations = $this->getTranslations();

        // Pass translations to the Blade view and render it
        return View::make('hyde::pages.default', [
            'translations' => $translations,
            'language' => $this->language, // Add language info for use in views
        ])->render();
    }

    /**
     * Retrieves translations for the page from the respective language's JSON file.
     * Falls back to the default language (e.g., 'en') if the translation is not found.
     *
     * @return array
     */
    protected function getTranslations(): array
    {
        // Determine the path to the translation file based on the current language
        $translationFile = resource_path("translations/{$this->language}.json");

        // If the translation file exists, load it
        if (File::exists($translationFile)) {
            return json_decode(File::get($translationFile), true);
        }

        // If the translation file doesn't exist, fall back to the default language (e.g., 'en')
        $defaultLanguage = config('hyde-multilanguage.default_language', 'en');
        $defaultTranslationFile = resource_path("translations/{$defaultLanguage}.json");

        // If the default language file exists, use it as a fallback
        if (File::exists($defaultTranslationFile)) {
            return json_decode(File::get($defaultTranslationFile), true);
        }

        // If no translation files are found, return an empty array
        return [];
    }
}
