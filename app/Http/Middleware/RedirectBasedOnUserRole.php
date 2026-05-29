<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RedirectBasedOnUserRole
{
    public function handle(Request $request, Closure $next): Response
    {
        // Redirect to the authenticated user's role panel
        if (auth()->check()) {
            $user = auth()->user();
            $role = $user->getRoleNames()->first();

            if ($role) {
                $rolePath = mb_strtolower($role);
                $requestedPanel = $request->segment(1);
                $panelPrefixes = ['admin', 'store_admin', 'store_driver', 'store_vendor', 'user'];

                if ($requestedPanel && in_array($requestedPanel, $panelPrefixes, true) && $requestedPanel !== $rolePath) {
                    return redirect("/{$rolePath}/dashboard");
                }

                if ($requestedPanel === $rolePath && $request->path() === $rolePath) {
                    return redirect("/{$rolePath}/dashboard");
                }
            }
        }

        return $next($request);
    }
}
