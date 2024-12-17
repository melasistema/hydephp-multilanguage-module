<?php

declare(strict_types=1);

namespace Melasistema\HydeMultilanguageModule\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;


class MakeMultilingualPageCommand extends Command
{
    protected $signature = 'make:multilanguage-page {page? : The name of the page file to create. Will be used to generate the slug}';
    protected $description = 'Create a page and its translation files for multi-language support';

    /**
     * The page title.
     */
    protected string $page;

    public function handle(): void
    {
        $page = Str::slug($this->argument('page')); // Ensure valid slug
        $pagesDirectory = base_path('_pages');
        $translationsDirectory = resource_path('lang');

        // Ensure required directories exist
        if (!File::exists($pagesDirectory)) {
            File::makeDirectory($pagesDirectory, 0755, true);
        }

        if (!File::exists($translationsDirectory)) {
            File::makeDirectory($translationsDirectory, 0755, true);
        }

        // Get the default language and supported languages from the configuration
        $defaultLanguage = config('hyde-multilanguage.default_language', 'en');
        $supportedLanguages = config('hyde-multilanguage.supported_languages', ['it', 'de']);

        // Create the page for the default language in the root _pages directory
        $this->createPageForLanguage($defaultLanguage, $page, $pagesDirectory, $translationsDirectory);

        // Create symlinks for each supported language to the original page in the root _pages directory
        foreach ($supportedLanguages as $language) {
            $this->createLanguageSymlink($language, $page, $pagesDirectory);
        }

        $this->info("Page and translations created successfully!");
    }

    /**
     * Create the page and translations for a specific language.
     */
    protected function createPageForLanguage(string $language, string $page, string $pagesDirectory, string $translationsDirectory): void
    {
        // For the default language (e.g., 'en'), create the page directly in the root of _pages
        if ($language === config('hyde-multilanguage.default_language')) {
            $pagePath = "{$pagesDirectory}/{$page}.blade.php";
            if (!File::exists($pagePath)) {
                File::put($pagePath, $this->generatePageTemplate($page, $language));
                $this->info("Page created at {$pagePath}");
            } else {
                $this->warn("Page already exists at {$pagePath}");
            }
        }

        // Create or update the translation files for the language
        $this->createTranslationFile($language, $page, $translationsDirectory);
    }

    /**
     * Create symlink for the language-specific page in its respective folder.
     */
    protected function createLanguageSymlink(string $language, string $page, string $pagesDirectory): void
    {
        // Path for the symlink in the language-specific folder
        $languageDirectory = "{$pagesDirectory}/{$language}";
        $originalPagePath = "{$pagesDirectory}/{$page}.blade.php";
        $languagePagePath = "{$languageDirectory}/{$page}.blade.php";

        // Ensure the language directory exists
        if (!File::exists($languageDirectory)) {
            File::makeDirectory($languageDirectory, 0755, true);
        }

        // Create symlink to the original page using PHP's symlink function
        if (!file_exists($languagePagePath)) {
            if (symlink($originalPagePath, $languagePagePath)) {
                $this->info("Symlink created at {$languagePagePath} pointing to {$originalPagePath}");
            } else {
                $this->error("Failed to create symlink at {$languagePagePath}");
            }
        } else {
            $this->warn("Symlink already exists at {$languagePagePath}");
        }
    }


    /**
     * Generate the template for the page.
     */
    protected function generatePageTemplate(string $page, string $language): string
    {
        return <<<BLADE
@extends('hyde::layouts.app')

@section('content')
    <h1>{{ __('pages.{$language}.{$page}.title') }}</h1>
    <p>{{ __('pages.{$language}.{$page}.content') }}</p>
@endsection
BLADE;
    }

    /**
     * Create or update the translation file for the language.
     */
    protected function createTranslationFile(string $language, string $page, string $translationsDirectory): void
    {
        // Set the path for the translation JSON file
        $translationPath = "{$translationsDirectory}/{$language}.json";

        // Load or create the existing translation
        $translations = $this->loadOrCreateTranslations($translationPath);

        // Add or update the translation for the page
        if (!isset($translations["pages.{$language}.{$page}.title"]) || !isset($translations["pages.{$language}.{$page}.content"])) {
            $translations["pages.{$language}.{$page}.title"] = ucfirst($page);
            $translations["pages.{$language}.{$page}.content"] = "This is the content for the {$page} page in {$language}.";
            $this->saveTranslations($translationPath, $translations);
            $this->info("Translation added for '{$page}' in {$language}");
        } else {
            $this->warn("Translation already exists for '{$page}' in {$language}");
        }
    }

    /**
     * Load existing translations or create a new empty array.
     */
    protected function loadOrCreateTranslations(string $filePath): array
    {
        if (File::exists($filePath)) {
            return json_decode(File::get($filePath), true) ?? [];
        }

        return [];
    }

    /**
     * Save translations back to the JSON file.
     */
    protected function saveTranslations(string $filePath, array $translations): void
    {
        File::put($filePath, json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
