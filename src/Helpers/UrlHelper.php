<?php

declare(strict_types=1);

namespace Melasistema\HydeMultilanguageModule\Helpers;

/**
 * Class UrlHelper
 * @package Melasistema\HydeMultilanguageModule\Helpers
 */
class UrlHelper
{
    /**
     * Generate a localized URL.
     *
     * @param string $path
     * @param string|null $locale
     * @return string
     */
    function localized_url($path, $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        return url($locale . '/' . ltrim($path, '/'));
    }
}
