<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Helpers\GeneralResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

class checkPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $jwtPayload = $request->get('jwt_payload');
        $routePermission = $request->route()->getName();

        if (!$routePermission) {
            return GeneralResponse::error(
                statusCode: 403,
                errorCode: '15'
            );
        }

        $permissions = collect($jwtPayload->get('permissions', []));

        // Full access
        if ($permissions->contains('*')) {
            return $next($request);
        }

        // Exact match
        if ($permissions->contains($routePermission)) {
            return $next($request);
        }

        // Wildcard match (category.* → category.view)
        $allowed = $permissions->contains(
            fn ($perm) => Str::is($perm, $routePermission)
        );

        if (!$allowed) {
            return GeneralResponse::error(
                statusCode: 403,
                errorCode: '15'
            );
        }

        return $next($request);
    }
}
