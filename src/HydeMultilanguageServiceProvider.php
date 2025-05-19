<?php

declare(strict_types=1);

namespace Melasistema\HydeMultilanguageModule;

use Hyde\Foundation\Facades\Routes;
use Hyde\Pages\BladePage;
use Hyde\Pages\Concerns\HydePage;
use Illuminate\Support\ServiceProvider;
use Illuminate\Translation\TranslationServiceProvider;
use Melasistema\HydeMultilanguageModule\Middleware\SetLocale;
use Melasistema\HydeMultilanguageModule\Models\MultilingualRoute;
use Melasistema\HydeMultilanguageModule\Pages\MultilingualBladePage;

class HydeMultilanguageServiceProvider extends ServiceProvider
{
    /**
     * @throws \Throwable
     */
    public function register(): void
    {
        echo "--- Entering HydeMultilanguageServiceProvider::register() ---\n"; // DEBUG
        try {
            // Merge package configuration
            $this->mergeConfigFrom(__DIR__ . '/../config/hyde-multilanguage-module.php', 'hyde-multilanguage');

            // Register console commands if running in console
            if ($this->app->runningInConsole()) {
                $this->commands([
                    /*Commands\MakeMultilingualPageCommand::class,*/
                ]);
            }

            // Ensure your custom TranslationService is bound in the container
            // This is generally safe in register().
            if (! $this->app->bound(Services\TranslationService::class)) {
                $this->app->singleton(Services\TranslationService::class, function ($app) {
                    return new Services\TranslationService();
                });
            }

            echo "--- Exiting HydeMultilanguageServiceProvider::register() successfully ---\n"; // DEBUG
        } catch (\Throwable $e) {
            // If an exception occurs in register, print it and re-throw
            echo "--- Exception in HydeMultilanguageServiceProvider::register(): " . $e->getMessage() . " ---\n"; // DEBUG
            throw $e; // Re-throw the exception so Composer/Hyde sees the failure
        }

    }

    /**
     * @throws \Throwable
     */
    public function boot(): void
    {

        echo "--- Entering HydeMultilanguageServiceProvider::boot() ---\n"; // DEBUG
        try {
            // Publish package assets (just registers paths, actual publishing happens later)
            $this->publishAssets();

            // Register middleware (this logic is deferred until application booted)
            $this->registerMiddleware();

            // **REVERT: Call route registration directly from boot()**
            // This version of the boot method was discoverable by package:discover.
            $this->registerMultilanguageRoutes();


            // **NEW: Attempt to explicitly boot TranslationServiceProvider if registered but not booted**
            // This tries to ensure the 'translator' binding is fully functional before page compilation.
            $translationProvider = $this->app->getProvider(TranslationServiceProvider::class);

            if ($translationProvider && ! $translationProvider->isBooted()) {
                echo "--- Attempting to explicitly boot TranslationServiceProvider ---\n"; // DEBUG
                // These calls ensure the provider's boot logic runs.
                $translationProvider->callBootingCallbacks(); // Call any booting callbacks
                $translationProvider->boot(); // Call the provider's boot method
                $translationProvider->callBootedCallbacks(); // Call any booted callbacks
                echo "--- Successfully booted TranslationServiceProvider ---\n"; // DEBUG
            } else {
                echo "--- TranslationServiceProvider not found or already booted ---\n"; // DEBUG
                }


            echo "--- Exiting HydeMultilanguageServiceProvider::boot() successfully ---\n"; // DEBUG
        } catch (\Throwable $e) {
            // If an exception occurs in boot, print it and re-throw
            echo "--- Exception in HydeMultilanguageServiceProvider::boot(): " . $e->getMessage() . " ---\n"; // DEBUG
            throw $e; // Re-throw the exception
        }
    }

    /**
     * Register the multilanguage routes.
     */
    protected function registerMultilanguageRoutes(): void
    {
        $supportedLanguages = config('hyde-multilanguage.translations', ['it', 'de']);
        $defaultLocale = config('hyde-multilanguage.default_language', 'en');

        // Get original routes discovered by Hyde
        $initialRoutes = Routes::all()->collect();

        // **Initial Debugging Dump (keep for now to confirm initial state)**
        echo '--- Initial Routes Collection State (Before Filter/Processing) ---\n';
        echo 'Initial routes count: ' . $initialRoutes->count() . "\n";
        echo 'Initial route keys: ' . $initialRoutes->keys()->implode(', ') . "\n";
        echo "Initial Details:\n";
        $initialRoutes->each(function ($route) {
            echo '  - Type: ' . get_class($route) . "\n";
            // This instanceof check was failing but we are keeping the debug line
            echo '    Is instance of Hyde\\Support\\Models\\Route: ' . (int)($route instanceof BaseRoute) . "\n";
            try {
                // Use method_exists for safer access in this debug loop
                if (method_exists($route, 'getRouteKey')) { echo '    Key: ' . $route->getRouteKey() . "\n"; }
                // getUri() does not exist on BaseRoute, avoid calling here
                if (method_exists($route, 'getPageIdentifier')) { echo '    Page Identifier: ' . $route->getPageIdentifier() . "\n"; }
            } catch (\Throwable $e) { echo '    Error accessing route details: ' . $e->getMessage() . " ---\n"; } // Catch potential errors
            echo "----------------------------------------------------\n";
        });
        echo "--- End Initial Routes Collection State ---\n";


        // Get routes to process after checking initial state
        // **FIX: Use get_class() check instead of instanceof to avoid issues**
        $routesToProcess = $initialRoutes->filter(function ($route) {
            // Filter to process only BaseRoute instances by checking the class name string.
            // This bypasses the potentially faulty instanceof behavior.
            return get_class($route) === \Hyde\Support\Models\Route::class;
        });


        echo 'Processing ' . $routesToProcess->count() . ' original routes for multilingual variants.' . "\n";


        // Iterate over each valid original route (This loop should now run if filter works)
        // Use the correct type hint for clarity and method access
        $routesToProcess->each(function (\Hyde\Support\Models\Route $route) use ($supportedLanguages, $defaultLocale) {

            $originalPage = $route->getPage();
            $originalRouteKey = $route->getRouteKey(); // Correct method call
            // Derive originalUri - Note: BaseRoute doesn't have getUri(), but MultilingualRoute does
            // We need the URI to set it on the MultilingualRoute we create.
            $originalUri = ($originalRouteKey === 'index') ? '/' : $originalRouteKey;


            // --- Handle Default Language Route (Replace Original) ---
            // Create a MultilingualBladePage instance for the default locale.
            $defaultLocalePage = $this->createLocalizedPageForRoute($originalPage, $defaultLocale);

            // Create a new MultilingualRoute for the default locale using the original key/uri.
            $defaultMultilingualRoute = (new MultilingualRoute($defaultLocalePage))
                ->setKey($originalRouteKey) // Use original key (e.g., 'index')
                ->setUri($originalUri);     // Use the derived original URI (e.g., '/')

            // Add this new MultilingualRoute. Since the key is the original key,
            // this will overwrite the original BaseRoute using Collection::put().
            Routes::addRoute($defaultMultilingualRoute);
            echo "Registered/Replaced default route '{$originalRouteKey}' with MultilingualRoute ({$defaultLocale}).\n";


            // --- Handle Other Languages Routes ---
            foreach ($supportedLanguages as $locale) {
                if ($locale === $defaultLocale) {
                    continue;
                }

                $localizedRouteKey = "{$locale}/{$originalRouteKey}";
                $localizedUri = $localizedRouteKey; // URI matches key

                $localizedPage = $this->createLocalizedPageForRoute($originalPage, $locale);

                $multilingualRoute = (new MultilingualRoute($localizedPage))
                    ->setKey($localizedRouteKey)
                    ->setUri($localizedUri);

                // Add the new route.
                Routes::addRoute($multilingualRoute);
                echo "Registered multilingual route: '{$localizedRouteKey}'.\n"; // This echo confirms loop execution
            }
        });

        // **Final Routes Collection State Dump (keep for confirmation)**
        echo "--- Final Routes Collection State in Service Provider ---\n";
        $finalRoutes = Routes::all()->collect();
        echo 'Final routes count: ' . $finalRoutes->count() . "\n";
        echo 'Final route keys: ' . $finalRoutes->keys()->implode(', ') . "\n";
        echo "Details:\n";
        $finalRoutes->each(function ($route) use ($defaultLocale) {
            // Use get_class check here as well due to instanceof issues
            if (get_class($route) === \Hyde\Support\Models\Route::class || $route instanceof MultilingualRoute) { // Check for both types we expect
                echo "  - Key: {$route->getRouteKey()}, ";
                // Safely derive URI for the dump based on key
                echo "URI: ";
                // Use getUri() if it's a MultilingualRoute, otherwise derive
                if ($route instanceof MultilingualRoute) {
                    echo $route->getUri();
                } else { // BaseRoute or other, derive
                    echo ($route->getRouteKey() === 'index') ? '/' : $route->getRouteKey();
                }

                echo ", Page Identifier: {$route->getPageIdentifier()}";

                $pageLocale = 'N/A';
                if ($route->getPage() instanceof MultilingualBladePage) {
                    $pageLocale = $route->getPage()->locale;
                } elseif ($route->getPage() instanceof HydePage) {
                    $pageLocale = $defaultLocale . ' (implicit)';
                }
                echo ", Page Class: " . get_class($route->getPage()) . ", Page Locale: {$pageLocale}\n";
            } else {
                echo '  - Unexpected object in collection: ' . get_class($route) . "\n";
            }
        });
        echo "-------------------------------------------------------\n";
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
     * Create a localized page for a given route and locale.
     *
     * @param HydePage $page
     * @param string $locale
     * @return HydePage
     */
    protected function createLocalizedPageForRoute($page, string $locale): mixed
    {
        // $page here is the original page object (BladePage, MarkdownPage, etc.)
        // We need its identifier to create the new MultilingualBladePage.
        $identifier = $page->getIdentifier(); // **Ensure this is getIdentifier()**

        return match (true) {
            $page instanceof BladePage => new MultilingualBladePage($identifier, $locale),
            // Handle other page types if needed, creating multilingual versions of them
            default => new MultilingualBladePage($identifier, $locale), // Assuming default handling is MultilingualBladePage
        };
    }

    /**
     * Publish the package assets.
     */
    protected function publishAssets(): void
    {
        $this->publishes([
            __DIR__ . '/../config/hyde-multilanguage-module.php' => config_path('hyde-multilanguage.php'),
            __DIR__ . '/../resources/lang' => resource_path('lang/vendor/hyde-multilanguage'),
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
