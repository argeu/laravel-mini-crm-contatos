<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            $status = 500;
            $message = 'An unexpected error occurred';

            if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
                $status = 401;
                $message = 'Unauthenticated';
            } elseif ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                $status = 404;
                $message = 'Resource not found';
            } elseif ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                $status = 404;
                $message = 'Endpoint not found';
            } elseif ($exception instanceof \Illuminate\Validation\ValidationException) {
                $status = 422;
                $message = 'Validation failed';
            } elseif ($exception instanceof \Illuminate\Database\QueryException) {
                $status = 500;
                $message = 'Database operation failed';
            } elseif (method_exists($exception, 'getStatusCode')) {
                $status = $exception->getStatusCode();
                $message = $exception->getMessage();
            } elseif ($exception instanceof \Exception) {
                $message = $exception->getMessage();
            }

            return response()->json([
                'error' => $message,
                'status' => $status,
                'timestamp' => now()->toISOString(),
            ], $status);
        }

        return parent::render($request, $exception);
    }
}
