<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\LoginData;
use App\DTO\RegisterData;
use App\DTO\TokenPayload;
use App\Enums\TokenType;
use App\Exceptions\Auth\InvalidCredentialsException;
use App\Repositories\UserRepositoryInterface;
use App\Services\Contracts\Auth\TokenTtlProviderInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

final readonly class AuthService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private TokenTtlProviderInterface $ttlProvider,
    ) {}

    public function register(RegisterData $data): string
    {
        $this->userRepository->create($data);

        /** @var string|false $token */
        $token = Auth::attempt([
            'email' => $data->email,
            'password' => $data->password,
        ]);

        if ($token === false) {
            throw new InvalidCredentialsException;
        }

        return $token;
    }

    public function login(LoginData $credentials): string
    {
        /** @var string|false $token */
        $token = Auth::attempt([
            'email' => $credentials->email,
            'password' => $credentials->password,
        ]);

        if ($token === false) {
            throw new InvalidCredentialsException;
        }

        return $token;
    }

    public function getTokenPayload(string $token): TokenPayload
    {
        return new TokenPayload(
            accessToken: $token,
            tokenType: TokenType::Bearer->value,
            expiresIn: $this->ttlProvider->accessTokenTtlSeconds(),
        );
    }


    public function currentUser(): Authenticatable
    {
        $user = Auth::user();

        if (! $user instanceof Authenticatable) {
            throw new RuntimeException('Unauthenticated user.');
        }

        return $user;
    }

    public function logout(): void
    {
        Auth::logout();
    }
}
