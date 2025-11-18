<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        App\Providers\AuthServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Security middleware applied globally
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeadersMiddleware::class,
        ]);

        // Rate limiting middleware for specific routes
        $middleware->alias([
            'super_admin' => \App\Http\Middleware\SuperAdminMiddleware::class,
            'content_admin' => \App\Http\Middleware\ContentAdminMiddleware::class,
            'event_admin' => \App\Http\Middleware\EventAdminMiddleware::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'rate_limit' => \App\Http\Middleware\RateLimitMiddleware::class,
            'audit_log' => \App\Http\Middleware\AuditLogMiddleware::class,
        ]);

        // Apply audit logging to admin routes
        $middleware->group('admin', [
            \App\Http\Middleware\AuditLogMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle security exceptions
        $exceptions->render(function (\App\Exceptions\SecurityException $e, $request) {
            return $e->render($request);
        });

        // Handle registration exceptions
        $exceptions->render(function (\App\Exceptions\RegistrationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'error' => 'REGISTRATION_ERROR'
                ], 422);
            }
            
            return redirect()->back()
                ->withErrors(['registration' => $e->getMessage()])
                ->withInput();
        });

        // Handle event capacity exceptions
        $exceptions->render(function (\App\Exceptions\EventCapacityException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'error' => 'CAPACITY_EXCEEDED'
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', $e->getMessage());
        });

        // Log security violations
        $exceptions->reportable(function (\App\Exceptions\SecurityException $e) {
            \Log::channel('security')->critical('Security Exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        });

        // Handle validation exceptions with security logging
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            // Log suspicious validation failures
            $suspiciousPatterns = ['<script', 'javascript:', 'eval(', 'union select'];
            $inputData = $request->all();
            
            foreach ($inputData as $key => $value) {
                if (is_string($value)) {
                    foreach ($suspiciousPatterns as $pattern) {
                        if (stripos($value, $pattern) !== false) {
                            \Log::channel('security')->warning('Suspicious input detected in validation', [
                                'field' => $key,
                                'pattern' => $pattern,
                                'ip' => $request->ip(),
                                'user_id' => auth()->id(),
                                'url' => $request->fullUrl()
                            ]);
                            break;
                        }
                    }
                }
            }
            
            return null; // Let Laravel handle normally
        });
    })->create();
