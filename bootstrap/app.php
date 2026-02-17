<?php

use App\Helpers\GeneralResponse;
use App\Http\Middleware\checkPermission;
use App\Http\Middleware\checkRole;
use App\Http\Middleware\ValidSubscription;
use App\Http\Middleware\VerifyJwtToken;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        # Set alias for custome middleware
        $middleware->alias([
            'checkRole' => checkRole::class,
            'checkPermission' => checkPermission::class,
            'jwt.verify' => VerifyJwtToken::class,
            'subs.check' => ValidSubscription::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
        $exceptions->render(function (Throwable $e, $request) {
            if (!$request->is('api/*')) {
                return null;
            }

            if (
                $e instanceof AuthenticationException ||
                $e instanceof UnauthorizedHttpException
            ) {
                $previous = $e->getPrevious();

                return match (true) {
                    $previous instanceof TokenExpiredException => GeneralResponse::error(
                        statusCode: 401,
                        statusDescription: 'Authentication token is expired',
                        errorCode: '11'
                    ),

                    $previous instanceof TokenInvalidException => GeneralResponse::error(
                        statusCode: 401,
                        statusDescription: 'Authentication token is invalid',
                        errorCode: '12'
                    ),

                    default => GeneralResponse::error(
                        statusCode: 401,
                        statusDescription: 'Authentication token not provided',
                        errorCode: '13'
                    ),
                };
            }

            return null;
        });
        
        $exceptions->render(function (ModelNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                return GeneralResponse::error(
                    statusCode: 404,
                    errorCode: '40'
                );
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                return GeneralResponse::error(
                    statusCode: 404,
                    errorCode: '40'
                );
            }
        });
    })->create();
