# HydePHP Multi Language Module

This package aims to bring robust multi-language capabilities to [HydePHP](https://hydephp.com/), a Laravel-powered static site generator, as a standalone Composer module. The goal is to enable developers to build multilingual static websites using familiar Blade templates and a custom translation management system.

## The Core Challenge: `BindingResolutionException` during Build

During the development and integration with HydePHP's static site generation process, a **persistent and critical issue** has been identified:

`Illuminate\Contracts\Container\BindingResolutionException: Target class [translator] does not exist.`

This error consistently occurs when HydePHP attempts to compile Blade pages using Laravel's `Illuminate\Support\Facades\View::make()->render()` method within the build command (`php hyde build`).

**Root Cause Analysis:**

HydePHP's build process operates in a console environment, which has a distinct application lifecycle compared to a standard web request. It appears that the Laravel application container, at the point when Blade views are rendered for static compilation, does not have the `Illuminate\Translation\TranslationServiceProvider` (which provides the `translator` binding) fully registered or booted in a manner that makes it accessible to the `View` factory.

**Attempts to Resolve (without bypassing `View::make()`):**

Extensive efforts have been made to ensure the `translator` binding is available before page compilation:
* **Explicit Service Provider Registration:** Directly calling `$this->app->register(TranslationServiceProvider::class);` and `$this->app->boot();` within the package's `HydeMultilanguageServiceProvider`.
* **Pre-Build Tasks:** Implementing a HydePHP `BuildTask` that runs before page compilation to register and boot the `TranslationServiceProvider`.

Despite these measures, the `BindingResolutionException` persists. This indicates a deeper incompatibility or timing issue within HydePHP's build lifecycle where the container's state, specifically concerning the translation service, is not stable or fully bootstrapped when the view rendering engine requires it.

---

**Hit the error:**
    
Consider a simple Blade file like `resources/views/pages/about.blade.php`

```blade
@extends('hyde::layouts.app')
@section('content')

    <main id="content" class="mx-auto max-w-7xl py-16 px-8">
        <h1 class="text-center text-3xl font-bold">
            {{ translate('welcome', $locale ?? config('hyde-multilanguage.default_language', 'en')) }}
        </h1>
    </main>

@endsection
```

## Credits

- **Author**: [Luca Visciola](https://github.com/melasistema) ([info@melasistema.com](mailto:info@melasistema.com))
- **HydePHP**: Created by [Caen De Silva](https://github.com/caendesilva) ([HydePHP GitHub](https://github.com/hydephp/hyde))

---

## License

This package is licensed under the MIT License. See the [LICENSE](./LICENSE.md) file for more details.
