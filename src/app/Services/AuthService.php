<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\LoginData;
use App\DTO\RegisterData;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

final class AuthService
{
    public function register(RegisterData $data): string
    {
        $user = User::create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => Hash::make($data->password),
        ]);

        return Auth::login($user);
    }

    public function attemptLogin(LoginData $credentials): ?string
    {
        if (! $token = Auth::attempt([
            'email' => $credentials->email,
            'password' => $credentials->password,
        ])) {
            return null;
        }

        return $token;
    }

    public function getTokenPayload(string $token): array
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60,
        ];
    }

    public function currentUser(): ?User
    {
        return Auth::user();
    }

    public function logout(): void
    {
        Auth::logout();
    }
}
