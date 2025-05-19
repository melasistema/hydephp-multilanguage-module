<?php

declare(strict_types=1);

namespace Melasistema\HydeMultilanguageModule\Pages;

use Hyde\Pages\BladePage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Melasistema\HydeMultilanguageModule\Services\TranslationService;

/**
 * Multilingual Blade Page.
 *
 * Extends the HydePHP BladePage class to add support for multilingual features.
 */
class MultilingualBladePage extends BladePage
{
    public string $locale = '';

    public function __construct(string $identifier, string $locale)
    {
        $originalIdentifier = str_replace($locale . '/', '', $identifier);
        parent::__construct($originalIdentifier);
        $this->locale = $locale;
    }

    public function compile(): string
    {
        Log::info('Compiling MultilingualBladePage: ' . $this->identifier);
        Log::info('Locale: ' . $this->locale);
        app()->setLocale($this->locale);

        $translationService = app(TranslationService::class);
        $translationService->loadTranslations($this->locale);

        $viewData = [];

        // Add the locale explicitly to the view data
        $viewData['locale'] = $this->locale;

        return View::make($this->getBladeView(), $viewData)->render();
    }

    public function getOutputPath(): string
    {
        $defaultLocale = config('hyde-multilanguage.default_language', 'en');
        if (isset($this->locale) && $this->locale === $defaultLocale) {
            return parent::getOutputPath();
        }

        return (isset($this->locale) ? $this->locale : $defaultLocale) . '/' . parent::getOutputPath();
    }
}