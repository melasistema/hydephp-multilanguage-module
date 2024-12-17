<?php

declare(strict_types=1);

namespace Melasistema\HydeMultilanguageModule\Helpers;

class UrlHelper
{
    function localized_url($path, $locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        return url($locale . '/' . ltrim($path, '/'));
    }

}