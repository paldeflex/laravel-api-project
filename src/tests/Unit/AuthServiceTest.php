<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTO\LoginData;
use App\DTO\RegisterData;
use App\Exceptions\InvalidCredentialsException;
use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use App\Services\AuthService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

final class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @throws BindingResolutionException
     */
    public function test_register_creates_user_and_returns_token(): void
    {
        /** @var AuthService $service */
        $service = $this->app->make(AuthService::class);

        $data = new RegisterData(
            name: 'John Doe',
            email: 'john@example.com',
            password: 'secret123',
        );

        $token = $service->register($data);

        $this->assertIsString($token);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
        ]);

        $this->assertInstanceOf(User::class, Auth::user());
        $this->assertEquals('john@example.com', Auth::user()->email);
    }

    /**
     * @throws BindingResolutionException
     */
    public function test_login_returns_token_for_valid_credentials(): void
    {
        /** @var AuthService $service */
        $service = $this->app->make(AuthService::class);

        /** @var UserRepositoryInterface $users */
        $users = $this->app->make(UserRepositoryInterface::class);

        $registerData = new RegisterData(
            name: 'Jane Doe',
            email: 'jane@example.com',
            password: 'password123',
        );

        $users->create($registerData);

        $loginData = new LoginData(
            email: 'jane@example.com',
            password: 'password123',
        );

        $token = $service->login($loginData);

        $this->assertIsString($token);
    }

    /**
     * @throws BindingResolutionException
     */
    public function test_login_throws_exception_for_invalid_credentials(): void
    {
        /** @var AuthService $service */
        $service = $this->app->make(AuthService::class);

        $loginData = new LoginData(
            email: 'notexists@example.com',
            password: 'wrong-password',
        );

        $this->expectException(InvalidCredentialsException::class);

        $service->login($loginData);
    }
}
