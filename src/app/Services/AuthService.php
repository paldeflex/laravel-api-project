<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\LoginData;
use App\DTO\RegisterData;
use App\DTO\TokenPayload;
use App\Enums\TokenType;
use App\Exceptions\InvalidCredentialsException;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Auth\Authenticatable;

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

    public function login(LoginData $credentials): string
    {
        $token = Auth::attempt([
            'email' => $credentials->email,
            'password' => $credentials->password,
        ]);

        if (! is_string($token)) {
            throw new InvalidCredentialsException;
        }

        return $token;
    }

    public function getTokenPayload(string $token): TokenPayload
    {
        return new TokenPayload(
            accessToken: $token,
            tokenType: TokenType::Bearer->value,
            expiresIn: config('jwt.ttl') * 60,
        );
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
