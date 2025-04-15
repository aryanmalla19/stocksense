<?php

use App\Http\Middleware\ApiExceptionMiddleware;
use App\Http\Middleware\IsAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'api.exception' => ApiExceptionMiddleware::class, // Single alias definition
            'cors' => HandleCors::class,
            'isAdmin' => IsAdmin::class
        ]);
        $middleware->throttleApi('60,1');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'The requested resource could not be found.',
                    'error' => 'Not Found',
                    'details' => $e->getMessage(),
                ], 404);
            }
        });

        $exceptions->render(function (Illuminate\Database\QueryException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid query parameters. Please check your input.',
                    'error' => 'QueryException',
                    'details' => config('app.debug') ? $e->getMessage() : null,
                ], 400);
            }
        });
        $exceptions->render(function (Illuminate\Validation\ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }
        });

        $exceptions->render(function (Illuminate\Auth\Access\AuthorizationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to perform this action.',
                ], 403);
            }
        });

        $exceptions->render(function (Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found.',
                    'model' => class_basename($e->getModel()),
                ], 404);
            }
        });

        $exceptions->render(function (Throwable $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'An unexpected error occurred.',
                    'error' => class_basename($e),
                    'details' => config('app.debug') ? $e->getMessage() : null,
                ], 500);
            }
        });

    })->create();
