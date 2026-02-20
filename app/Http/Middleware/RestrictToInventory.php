<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictToInventory
{
    private array $allowedRoutePatterns = [
        'supplier-inventories.*',
        'distributor_categories.*',
        'distributor_brands.*',
        'api.supplier-inventories.*',
        'home',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->isInventoryViewer()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if ($routeName && $this->isRouteAllowed($routeName)) {
            return $next($request);
        }

        return redirect()->route('supplier-inventories.index')
            ->with('error', 'No tenés permisos para acceder a esa sección.');
    }

    private function isRouteAllowed(string $routeName): bool
    {
        foreach ($this->allowedRoutePatterns as $pattern) {
            if (str_ends_with($pattern, '*')) {
                $prefix = rtrim($pattern, '.*');
                if (str_starts_with($routeName, $prefix)) {
                    return true;
                }
            } elseif ($routeName === $pattern) {
                return true;
            }
        }

        return false;
    }
}
