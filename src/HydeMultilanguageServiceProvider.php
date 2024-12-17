<?php

declare(strict_types=1);

namespace Melasistema\HydeMultilanguageModule;

use Hyde\Foundation\Facades\Routes;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\HtmlPage;
use Hyde\Pages\MarkdownPage;
use Illuminate\Support\ServiceProvider;
use Illuminate\Translation\TranslationServiceProvider;
use Melasistema\HydeMultilanguageModule\Middleware\SetLocale;
use Melasistema\HydeMultilanguageModule\Pages\MultilingualBladePage;
use Melasistema\HydeMultilanguageModule\Pages\MultilingualDocumentationPage;
use Melasistema\HydeMultilanguageModule\Pages\MultilingualHtmlPage;
use Melasistema\HydeMultilanguageModule\Pages\MultilingualMarkdownPage;
use Melasistema\HydeMultilanguageModule\Services\TranslationService;

/**
 * This package extends the functionality of HydePHP.
 * HydePHP is an elegant static site generator by Caen De Silva.
 *
 * @link https://github.com/hydephp/hyde
 *
 * This file is part of the Hyde Multi Language Module package.
 *
 * @package Melasistema\HydeMultilanguageModule
 * @author  Luca Visciola <info@melasistema.com>
 * @copyright 2024 Luca Visciola
 * @license MIT License
 *
 */

class HydeMultilanguageServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     *
     */
    public function register(): void
    {

        // Merge configuration or bind services here.
        $this->mergeConfigFrom(__DIR__ . '/../config/hyde-multilanguage-module.php', 'hyde-multilanguage');

        // Register translation services
        $this->app->register(TranslationServiceProvider::class);

        // Register your TranslationService singleton
        $this->app->singleton(TranslationService::class, function ($app) {
            return new TranslationService();
        });

        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\MakeMultilingualPageCommand::class,
                Commands\PublishMultilingualHomepageCommand::class,
            ]);
        }
    }

    /**
     * Bootstrap the service
     *
     * @return void
     */
    public function boot(): void
    {
        // Publish a configuration file
        $this->publishes([
            __DIR__ . '/../config/hyde-multilanguage-module.php' => config_path('hyde-multilanguage.php'),
        ], 'hyde-multilanguage-config');

        // Load and publish translations
        $this->loadTranslationsFrom(__DIR__ . '/resources/lang', 'hyde-multilanguage');
        $this->publishes([
            __DIR__ . '/resources/lang' => resource_path('lang/vendor/hyde-multilanguage'),
        ], 'hyde-multilanguage-translations');



        // Register the views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'hyde-multilanguage');
        // Publish the views to the resources/views/vendor directory
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/hyde-multilanguage'),
        ], 'hyde-multilanguage-views');

        // Publish the custom Tailwind config to the root of the project
        $this->publishes([
            __DIR__ . '/../tailwind-multilanguage.config.js' => base_path('tailwind-multilanguage.config.js'),
        ], 'tailwind-multilanguage-config');

        // Register middleware for locale handling
        $this->registerMiddleware();

        // Dynamically adjust routes to support multi-language prefixes
        $this->registerMultilanguageRoutes();

    }

    /**
     * Register middleware for locale setting.
     *
     * @return void
     */
    protected function registerMiddleware(): void
    {
        $this->app->booted(function () {
            // Only attempt to register if router exists
            if ($this->app->bound('router')) {
                $router = $this->app->make('router');
                $router->aliasMiddleware('setLocale', SetLocale::class);
            }
        });
    }

    /**
     * Register multilingual routes.
     *
     * @return void
     */
    protected function registerMultilanguageRoutes(): void
    {
        $supportedLanguages = config('hyde-multilanguage.supported_languages', ['en']);
        $defaultLocale = config('hyde-multilanguage.default_language', 'en');

        // Retrieve all defined routes
        $routes = Routes::all();

        // Iterate over each route
        $routes->each(function ($route) use ($supportedLanguages, $defaultLocale) {
            $baseLink = $route->getLink();

            foreach ($supportedLanguages as $locale) {
                $localizedLink = $locale === $defaultLocale ? $baseLink : "{$locale}/{$baseLink}";

                // Create a new MultilingualRoute instance for the localized route
                $localizedRoute = new \Melasistema\HydeMultilanguageModule\Models\MultilingualRoute($route->getPage());

                // Set the page for the localized route
                $localizedRoute->setPage($this->createLocalizedPageForRoute($route->getPage(), $locale));

                // Update the route properties for the localized route
                $localizedRoute->key = $localizedLink;
                $localizedRoute->uri = $localizedLink;

                // Add the localized route to the Routes collection
                Routes::addRoute($localizedRoute);
            }
        });
    }

    /**
     * Create a localized page for the given route.
     *
     * @param $page
     * @param string $locale The locale to create the page for.
     * @return MultilingualBladePage|MultilingualDocumentationPage|MultilingualHtmlPage|MultilingualMarkdownPage
     */
    protected function createLocalizedPageForRoute($page, string $locale)
    {
        if ($page instanceof BladePage) {
            return new MultilingualBladePage($page->identifier, $locale);
        } elseif ($page instanceof MarkdownPage) {
            return new MultilingualMarkdownPage($page->identifier, $locale);
        } elseif ($page instanceof HtmlPage) {
            return new MultilingualHtmlPage($page->identifier, $locale);
        } elseif ($page instanceof DocumentationPage) {
            return new MultilingualDocumentationPage($page->identifier, $locale);
        }

        return $page; // Fallback
    }

}
