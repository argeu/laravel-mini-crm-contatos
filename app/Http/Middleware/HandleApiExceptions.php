<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class HandleApiExceptions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        Log::info('HandleApiExceptions middleware is being executed');
        try {
            $response = $next($request);
            Log::info('HandleApiExceptions middleware - no exception caught');
            return $response;
        } catch (Throwable $exception) {
            Log::info('HandleApiExceptions middleware caught exception: ' . get_class($exception) . ' - ' . $exception->getMessage());
            return $this->handleException($exception, $request);
        }
    }

    /**
     * Handle the exception and return appropriate JSON response
     */
    protected function handleException(Throwable $exception, Request $request): JsonResponse
    {
        $message = $this->getErrorMessage($exception);
        $statusCode = $this->getStatusCode($exception);
        
        // Log the error with request context
        Log::error("Unhandled API Exception: " . $exception->getMessage(), [
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_id' => $request->user()?->id,
            'trace' => $exception->getTraceAsString()
        ]);

        return response()->json([
            'error' => $message,
            'status' => $statusCode,
            'timestamp' => now()->toISOString()
        ], $statusCode);
    }

    /**
     * Get appropriate error message based on exception type
     */
    protected function getErrorMessage(Throwable $exception): string
    {
        return match (true) {
            $exception instanceof ModelNotFoundException => 'Resource not found',
            $exception instanceof NotFoundHttpException => 'Endpoint not found',
            $exception instanceof ValidationException => 'Validation failed',
            $exception instanceof QueryException => 'Database operation failed',
            default => 'An unexpected error occurred'
        };
    }

    /**
     * Get appropriate HTTP status code based on exception type
     */
    protected function getStatusCode(Throwable $exception): int
    {
        return match (true) {
            $exception instanceof ModelNotFoundException => 404,
            $exception instanceof NotFoundHttpException => 404,
            $exception instanceof ValidationException => 422,
            $exception instanceof QueryException => 500,
            default => 500
        };
    }
} 