<?php

namespace App\Http\Controllers;

use App\DTO\LoginData;
use App\DTO\RegisterData;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService,
    ) {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $dto = new RegisterData(
            $data['name'],
            $data['email'],
            $data['password'],
        );

        $token = $this->authService->register($dto);

        return response()->json(
            $this->authService->getTokenPayload($token),
            201
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        $dto = new LoginData(
            $data['email'],
            $data['password'],
        );

        $token = $this->authService->attemptLogin($dto);

        if (! $token) {
            return response()->json(['message' => 'Неверные логин или пароль'], 401);
        }

        return response()->json(
            $this->authService->getTokenPayload($token)
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
