<?php

declare(strict_types=1);

namespace Melasistema\HydeMultilanguageModule\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class TranslationService
{
    protected array $translations = [];

    public function loadTranslations($lang): void
    {
        $filePath = resource_path("lang/vendor/hyde-multilanguage/{$lang}/message.php");

        if (File::exists($filePath)) {
            $this->translations[$lang] = include $filePath;
        } else {
            $this->translations[$lang] = [];
            Log::warning("Translation file not found: {$filePath}");
        }
    }

    public function get($key, $lang)
    {
        // Fallback to English if the translation is not found
        return $this->translations[$lang][$key] ?? $this->translations['en'][$key] ?? $key;
    }
}