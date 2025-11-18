<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class AuditLogMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        
        $response = $next($request);
        
        // Only log sensitive operations
        if ($this->shouldLog($request)) {
            $this->logActivity($request, $response, $startTime);
        }

        return $response;
    }

    /**
     * Determine if the request should be logged.
     */
    protected function shouldLog(Request $request): bool
    {
        // Log admin operations
        if ($request->is('admin/*')) {
            return true;
        }

        // Log authentication operations
        if ($request->is('login') || $request->is('register') || $request->is('logout')) {
            return true;
        }

        // Log registration and payment operations
        if ($request->is('registrations/*') || $request->is('payments/*')) {
            return true;
        }

        // Log data modification operations
        $modifyingMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];
        if (in_array($request->method(), $modifyingMethods)) {
            return true;
        }

        return false;
    }

    /**
     * Log the activity to database.
     */
    protected function logActivity(Request $request, Response $response, float $startTime): void
    {
        try {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            
            DB::table('admin_activity_logs')->insert([
                'user_id' => auth()->id(),
                'action' => $this->getActionDescription($request),
                'model_type' => $this->getModelType($request),
                'model_id' => $this->getModelId($request),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'status_code' => $response->getStatusCode(),
                'execution_time_ms' => $executionTime,
                'request_data' => $this->sanitizeRequestData($request),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Log error but don't break the request
            \Log::error('Failed to log audit activity: ' . $e->getMessage());
        }
    }

    /**
     * Get action description from request.
     */
    protected function getActionDescription(Request $request): string
    {
        $route = $request->route();
        if (!$route) {
            return $request->method() . ' ' . $request->path();
        }

        $action = $route->getActionName();
        $method = $request->method();
        
        // Extract controller method
        if (str_contains($action, '@')) {
            $parts = explode('@', $action);
            $method_name = end($parts);
            
            return match($method_name) {
                'store' => 'Created',
                'update' => 'Updated',
                'destroy' => 'Deleted',
                'approve' => 'Approved',
                'reject' => 'Rejected',
                default => ucfirst($method_name)
            };
        }

        return $method . ' ' . ($route->getName() ?? $request->path());
    }

    /**
     * Get model type from request.
     */
    protected function getModelType(Request $request): ?string
    {
        $route = $request->route();
        if (!$route) {
            return null;
        }

        // Check route parameters for model binding
        $parameters = $route->parameters();
        
        foreach ($parameters as $key => $value) {
            if (is_object($value) && method_exists($value, 'getTable')) {
                return get_class($value);
            }
        }

        // Infer from URL path
        $path = $request->path();
        if (str_contains($path, 'events')) return 'App\\Models\\Event';
        if (str_contains($path, 'fests')) return 'App\\Models\\Fest';
        if (str_contains($path, 'registrations')) return 'App\\Models\\Registration';
        if (str_contains($path, 'users')) return 'App\\Models\\User';
        if (str_contains($path, 'prayer-times')) return 'App\\Models\\PrayerTime';
        if (str_contains($path, 'gallery')) return 'App\\Models\\GalleryImage';

        return null;
    }

    /**
     * Get model ID from request.
     */
    protected function getModelId(Request $request): ?int
    {
        $route = $request->route();
        if (!$route) {
            return null;
        }

        $parameters = $route->parameters();
        
        foreach ($parameters as $key => $value) {
            if (is_object($value) && method_exists($value, 'getKey')) {
                return $value->getKey();
            }
            if (is_numeric($value)) {
                return (int) $value;
            }
        }

        return null;
    }

    /**
     * Sanitize request data for logging.
     */
    protected function sanitizeRequestData(Request $request): array
    {
        $data = $request->except([
            'password',
            'password_confirmation',
            '_token',
            '_method',
            'g-recaptcha-response'
        ]);

        // Limit data size
        $json = json_encode($data);
        if (strlen($json) > 2000) {
            return ['_truncated' => 'Request data too large to log'];
        }

        return $data;
    }
}
