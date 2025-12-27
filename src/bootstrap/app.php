<?php

declare(strict_types=1);

use App\Exceptions\InvalidCredentialsException;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\EnsureProductIsPublished;
use App\Http\Middleware\EnsureReviewBelongsToProduct;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(fn (Middleware $middleware) => $middleware->alias([
        'admin' => AdminMiddleware::class,
        'product.published' => EnsureProductIsPublished::class,
        'review.belongs-to-product' => EnsureReviewBelongsToProduct::class,
    ]))
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, Throwable $e): bool => $request->is('api/*') || $request->expectsJson()
        );

        $exceptions->stopIgnoring(AuthenticationException::class);

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            return response()->json(
                ['message' => 'Необходимо авторизоваться'],
                Response::HTTP_UNAUTHORIZED
            );
        });

        $exceptions->render(function (InvalidCredentialsException $e, Request $request) {
            return response()->json(
                ['message' => $e->getMessage()],
                $e->getStatusCode()
            );
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($e->getPrevious() instanceof ModelNotFoundException) {
                return response()->json(
                    ['message' => 'Объект не найден'],
                    Response::HTTP_NOT_FOUND
                );
            }
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            return response()->json(
                [
                    'message' => 'Данные некорректны',
                    'errors' => $e->errors(),
                ],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        });

        $exceptions->render(function (HttpException $e, Request $request) {
            $code = $e->getStatusCode();

            $msg = match ($code) {
                Response::HTTP_FORBIDDEN => 'Доступ запрещён',
                Response::HTTP_NOT_FOUND => 'Не найдено',
                default => $e->getMessage() ?: 'Ошибка',
            };

            return response()->json(
                ['message' => $msg],
                $code
            );
        });
    })
    ->create();
