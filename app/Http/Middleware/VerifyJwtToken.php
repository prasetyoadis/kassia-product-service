<?php

namespace App\Http\Middleware;

use App\Helpers\GeneralResponse;
use Closure;
// use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class VerifyJwtToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        try {
            $payload = JWTAuth::setToken(
                $request->bearerToken()
            )->getPayload();

            # inject payload ke request
            $request->attributes->set('jwt_payload', $payload);

        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            # Jika token sudah tidak valid
            return GeneralResponse::error(
                statusCode: 401,
                errorCode: '12'
            );
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            # Jika token sudah kadaluarsa
            return GeneralResponse::error(
                statusCode: 401,
                errorCode: '11'
            );
        }

        return $next($request);
    }
}
