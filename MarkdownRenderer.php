<?php

declare(strict_types=1);

namespace Melasistema\HydeMultilanguageModule;

use Melasistema\HydeMultilanguageModule\Services\TranslationService;
use Hyde\Hyde;
use Illuminate\Support\Facades\View;

class MarkdownRenderer
{
    protected $translationService;

    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    public function render($page, $lang)
    {
        // Load the markdown file content
        $content = file_get_contents(base_path("_pages/{$page}.md"));

        // Replace translation placeholders with real content
        preg_match_all('/{{\s*__\([\'"]([^\'"]+)[\'"]\)\s*}}/', $content, $matches);

        foreach ($matches[1] as $key) {
            $content = str_replace("{{ __('{$key}') }}", $this->translationService->get($key, $lang), $content);
        }

        return $content;
    }
}
