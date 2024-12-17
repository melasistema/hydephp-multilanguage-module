<?php

declare(strict_types=1);

namespace Melasistema\HydeMultilanguageModule\Navigation;

use Hyde\Framework\Features\Navigation\BaseNavigationMenu;
use Hyde\Framework\Features\Navigation\NavItem;
use Hyde\Support\Models\Route;
use Hyde\Foundation\Facades\Routes;
use Illuminate\Support\Collection;

class CustomNavigationMenu extends BaseNavigationMenu
{
    protected function generate(): void
    {
        // Instead of passing Route objects directly, adapt route keys
        Routes::each(function ($routeKey): void {

            $route = Routes::get($routeKey); // Fetch the Route object based on the route key

            $routeKey = $route->getRouteKey();  // Assuming such a method exists

            if ($route && $this->canAddRoute($route)) {
                $this->items->put($routeKey, NavItem::fromRoute($route));
            }
        });

        // Add custom navigation items as usual
        collect(config('hyde.navigation.custom', []))->each(function ($item): void {
            $this->items->push($item);
        });
    }
}