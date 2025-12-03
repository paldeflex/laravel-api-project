<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = Auth::login($user);

        return [$user, $token];
    }

    public function attemptLogin(array $credentials): ?string
    {
        if (! $token = Auth::attempt($credentials)) {
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
