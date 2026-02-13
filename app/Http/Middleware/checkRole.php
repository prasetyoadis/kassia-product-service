<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\GeneralResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

class checkRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $jwtPayload = $request->get('jwt_payload');
        
        $userRoles = collect($jwtPayload->get('roles', []));

        // cek apakah user punya salah satu role yang diizinkan
        $hasRole = $userRoles->intersect($roles)->isNotEmpty();
        if (!$hasRole) {
            return GeneralResponse::error(
                statusCode: 403,
                errorCode: '15'
            );
        }

        return $next($request);
    }
}
