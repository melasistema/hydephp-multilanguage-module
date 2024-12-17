<?php

declare(strict_types=1);

namespace Melasistema\HydeMultilanguageModule\Models;

use Hyde\Support\Models\Route as BaseRoute;
use Hyde\Pages\Concerns\HydePage;

/**
 * This class extends the original Route class and allows access to the page for localization purposes.
 */
class MultilingualRoute extends BaseRoute
{
    public function __construct(HydePage $page)
    {
        parent::__construct($page);
    }

    /**
     * Set a new page instance to the route.
     */
    public function setPage(HydePage $page): void
    {
        $this->page = $page;
    }

    /**
     * Get the page associated with the route.
     */
    public function getPage(): HydePage
    {
        return $this->page;
    }
}
