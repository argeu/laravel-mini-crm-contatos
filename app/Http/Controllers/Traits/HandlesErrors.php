<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

trait HandlesErrors
{
    /**
     * Handle exceptions and return appropriate JSON response
     */
    protected function handleException(Throwable $exception, string $context = ''): JsonResponse
    {
        $message = $this->getErrorMessage($exception);
        $statusCode = $this->getStatusCode($exception);
        
        // Log the error with context
        Log::error("Error in {$context}: " . $exception->getMessage(), [
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
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

    /**
     * Create a success response with optional message
     */
    protected function successResponse($data = null, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        $response = ['message' => $message];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        return response()->json($response, $statusCode);
    }

    /**
     * Create an error response
     */
    protected function errorResponse(string $message, int $statusCode = 400): JsonResponse
    {
        return response()->json([
            'error' => $message,
            'status' => $statusCode,
            'timestamp' => now()->toISOString()
        ], $statusCode);
    }
} 