<?php

declare(strict_types=1);

namespace Melasistema\HydeMultilanguageModule;

use Hyde\Foundation\Facades\Routes;
use Hyde\Pages\BladePage;
use Illuminate\Support\ServiceProvider;
use Melasistema\HydeMultilanguageModule\Middleware\SetLocale;
use Melasistema\HydeMultilanguageModule\Models\MultilingualRoute;
use Melasistema\HydeMultilanguageModule\Pages\MultilingualBladePage;

class HydeMultilanguageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/hyde-multilanguage-module.php', 'hyde-multilanguage');

        if ($this->app->runningInConsole()) {
            $this->commands([
                /*Commands\MakeMultilingualPageCommand::class,*/
            ]);
        }
    }

    public function boot(): void
    {
        $this->publishAssets();
        $this->registerMiddleware();
        $this->registerMultilanguageRoutes();
    }

    /**
     * Register the multilanguage routes.
     */
    protected function registerMultilanguageRoutes(): void
    {
        $supportedLanguages = config('hyde-multilanguage.translations', ['it', 'de']);
        $defaultLocale = config('hyde-multilanguage.default_language', 'en');

        // Retrieve all defined routes
        $routes = Routes::all();

        // Iterate over each route
        $routes->each(function ($route) use ($supportedLanguages, $defaultLocale) {
            $originalPage = $route->getPage(); // Original page object

            foreach ($supportedLanguages as $locale) {
                // Skip creating routes for the default locale if already present
                if ($locale === $defaultLocale && $route->key === $route->getLink()) {
                    continue;
                }

                // Generate the localized link and create a localized route
                $localizedLink = $this->generateLocalizedLink($route->getLink(), $locale, $defaultLocale);
                $localizedPage = $this->createLocalizedPageForRoute($originalPage, $locale);

                // Create and register the multilingual route
                Routes::addRoute(
                    (new MultilingualRoute($localizedPage))
                        ->setKey($localizedLink)  // Correct the route key
                        ->setUri($localizedLink)  // Correct the URI
                );
            }
        });
    }

    /**
     * Generate a localized link for a given base link and locale.
     */
    protected function generateLocalizedLink(string $baseLink, string $locale, string $defaultLocale): string
    {
        // Add the locale as a prefix only if it's not the default language
        return $locale === $defaultLocale ? $baseLink : "{$locale}/{$baseLink}";
    }

    /**
     * Create a localized page instance for a given route.
     */
    protected function createLocalizedPageForRoute($page, string $locale): mixed
    {
        return match (true) {
            $page instanceof BladePage => new MultilingualBladePage($page->identifier, $locale),
            default => new MultilingualBladePage($page->identifier, $locale),
        };
    }

    /**
     * Publish the package assets.
     */
    protected function publishAssets(): void
    {
        $this->publishes([
            __DIR__ . '/../config/hyde-multilanguage-module.php' => config_path('hyde-multilanguage.php'),
            __DIR__ . '/resources/lang' => resource_path('lang/vendor/hyde-multilanguage'),
            __DIR__ . '/../resources/views' => resource_path('views/vendor/hyde-multilanguage'),
            __DIR__ . '/../tailwind-multilanguage.config.js' => base_path('tailwind-multilanguage.config.js'),
        ], 'hyde-multilanguage-assets');
    }

    /**
     * @return void
     */
    protected function registerMiddleware(): void
    {
        $this->app->booted(function () {
            if ($this->app->bound('router')) {
                $router = $this->app->make('router');
                $router->aliasMiddleware('setLocale', SetLocale::class);
            }
        });
    }

}
