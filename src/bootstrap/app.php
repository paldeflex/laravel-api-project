<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\EnsureProductIsPublished;
use App\Http\Middleware\EnsureReviewBelongsToProduct;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(fn ($m) => $m->alias([
        'admin' => AdminMiddleware::class,
        'product.published' => EnsureProductIsPublished::class,
        'review.belongs-to-product' => EnsureReviewBelongsToProduct::class,
    ]))
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(fn (Request $request, Throwable $e): bool =>
            $request->is('api/*') || $request->expectsJson()
        );

        $exceptions->stopIgnoring(AuthenticationException::class);
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            return response()->json(['message' => 'Необходимо авторизоваться'], 401);
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($e->getPrevious() instanceof ModelNotFoundException) {
                return response()->json(['message' => 'Объект не найден'], 404);
            }
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            return response()->json([
                'message' => 'Данные некорректны',
                'errors' => $e->errors(),
            ], 422);
        });

        $exceptions->render(function (HttpException $e, Request $request) {
            $code = $e->getStatusCode();
            $msg = match ($code) {
                403 => 'Доступ запрещён',
                404 => 'Не найдено',
                default => $e->getMessage() ?: 'Ошибка',
            };
            return response()->json(['message' => $msg], $code);
        });

        // Catch all — 500
        $exceptions->render(function (Throwable $e, Request $request) {
            return response()->json(['message' => 'Внутренняя ошибка сервера'], 500);
        });
    })
    ->create();
