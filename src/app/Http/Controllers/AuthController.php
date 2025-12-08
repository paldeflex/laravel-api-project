<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTO\LoginData;
use App\DTO\RegisterData;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $dto = RegisterData::fromArray($request->validated());

        $token = $this->authService->register($dto);

        return response()->json(
            $this->authService->getTokenPayload($token),
            Response::HTTP_CREATED
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $dto = LoginData::fromArray($request->validated());

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
