<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Manejo mejorado de errores de conexión MySQL
        $exceptions->render(function (\Illuminate\Database\QueryException $e, \Illuminate\Http\Request $request) {
            // Si es un error de conexión rechazada, proporcionar mensaje más claro
            if (str_contains($e->getMessage(), 'Connection refused') || 
                str_contains($e->getMessage(), 'SQLSTATE[HY000] [2002]')) {
                
                \Illuminate\Support\Facades\Log::error('Error de conexión MySQL: ' . $e->getMessage(), [
                    'url' => $request->fullUrl(),
                    'user_agent' => $request->userAgent(),
                    'ip' => $request->ip(),
                ]);
                
                // En producción, mostrar mensaje genérico al usuario
                if (app()->environment('production')) {
                    return response()->view('errors.database-connection', [], 503);
                }
            }
            
            return null; // Dejar que Laravel maneje otros errores normalmente
        });
    })->create();
