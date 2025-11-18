<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SecurityException extends Exception
{
    protected $code = 403;
    
    public function __construct(string $message = 'Security violation detected', int $code = 403, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(Request $request): Response
    {
        // Log security violation
        \Log::warning('Security Exception: ' . $this->getMessage(), [
            'ip' => $request->ip(),
            'user_id' => auth()->id(),
            'url' => $request->fullUrl(),
            'user_agent' => $request->userAgent(),
            'trace' => $this->getTraceAsString()
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Security violation detected',
                'error' => 'SECURITY_VIOLATION'
            ], $this->code);
        }

        return response()->view('errors.security', [
            'message' => $this->getMessage()
        ], $this->code);
    }
}
