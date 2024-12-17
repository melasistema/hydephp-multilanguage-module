<?php

declare(strict_types=1);

namespace Melasistema\HydeMultilanguageModule\Services;

use Illuminate\Support\Facades\File;

class TranslationService
{
    protected $translations = [];

    public function loadTranslations($lang)
    {
        $filePath = resource_path("lang/{$lang}.json");

        if (File::exists($filePath)) {
            $this->translations[$lang] = json_decode(File::get($filePath), true);
        } else {
            $this->translations[$lang] = [];
        }
    }

    public function get($key, $lang)
    {
        // Fallback to English if the translation is not found
        return $this->translations[$lang][$key] ?? $this->translations['en'][$key] ?? $key;
    }
}
