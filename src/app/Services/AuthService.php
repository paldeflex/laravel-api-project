<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\LoginData;
use App\DTO\RegisterData;
use App\DTO\TokenPayload;
use App\Enums\TokenType;
use App\Exceptions\InvalidCredentialsException;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

final readonly class AuthService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    public function register(RegisterData $data): string
    {
        $user = $this->userRepository->create($data);

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

    public function currentUser(): Authenticatable
    {
        return Auth::user();
    }

    public function logout(): void
    {
        Auth::logout();
    }
}
