<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTO\Auth\LoginData;
use App\DTO\Auth\RegisterData;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        /** @var array{name: string, email: string, password: string} $data */
        $data = $request->validated();

        $dto = RegisterData::fromArray($data);

        $token = $this->authService->register($dto);

        return response()->json(
            $this->authService->getTokenPayload($token),
            Response::HTTP_CREATED
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        /** @var array{email: string, password: string} $data */
        $data = $request->validated();

        $dto = LoginData::fromArray($data);

        $token = $this->authService->login($dto);

        return response()->json(
            $this->authService->getTokenPayload($token),
            Response::HTTP_OK
        );
    }

    public function me(): JsonResponse
    {
        return response()->json($this->authService->currentUser());
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return response()->json(['message' => 'Вы вышли из системы']);
    }
}
