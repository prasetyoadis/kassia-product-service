<?php

namespace App\Http\Middleware;

use App\Helpers\GeneralResponse;
// use App\Models\Reference\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
// use Tymon\JWTAuth\Facades\JWTAuth;

class ValidSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        # Get Token from header Authorization Bearer
        $token = $request->bearerToken();

        # Kalau token gak ada
        if (!$token) {
            return GeneralResponse::error(
                statusCode: 401,
                errorCode: '13'
            );
        }

        try {
            # Hit API kassia-user-service /auth/session
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(5)
                ->get(env('USER_SERVICE_URL', 'http://192.168.43.6:8001') . '/api/auth/session');

            # Kalau kassia-user-service /auth/session bukan 200
            if (!$response->successful()) {
                return GeneralResponse::error(
                    statusCode: 401,
                    errorCode: '14'
                );
            }

            $json = $response->json();

            # Pastikan JSON valid dan punya structure benar
            if (
                !isset($json['result']['errorCode']) ||
                $json['result']['errorCode'] !== '08'
            ) {
                return GeneralResponse::error(
                    statusCode: 401,
                    errorCode: '14'
                );
            }
        } catch (\Throwable $e) {
            Log::error('ValidSubscriptionMiddleware HTTP error', [
                'error' => $e->getMessage()
            ]);

            return GeneralResponse::error(
                statusCode: 503,
                errorCode: '74'
            );
        }
    
        return $next($request);
    }
}
