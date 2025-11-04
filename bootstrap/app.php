<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log; 
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface; 

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (Throwable $e, $request) {
            
            // Only run this custom logic IF we are in production (APP_DEBUG=false)
            if (!config('app.debug')) {
                
                $isHttpException = $e instanceof HttpExceptionInterface;
                
                // Check if it's a 500-level error
                if (!$isHttpException || ($isHttpException && $e->getStatusCode() >= 500)) {
                    
                    // 1. Generate a unique ID for this error
                    $errorId = (string) Str::uuid();

                    // 2. Log the full error to Railway, tagged with our ID
                    Log::error(
                        $e->getMessage() . ' [Error ID: ' . $errorId . ']',
                        // This includes the full stack trace in the log
                        ['trace' => $e->getTraceAsString()] 
                    );

                    // 3. Return our custom 500 view and pass it the ID
                    return response()->view('errors.500', [
                        'error_id' => $errorId // Pass this ID to the user
                    ], 500);
                }
            }
        });
    })->create();
