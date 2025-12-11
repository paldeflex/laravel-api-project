<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTO\RegisterData;
use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

final class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_creates_user_via_repository_and_logs_in(): void
    {
        $userRepository = Mockery::mock(UserRepositoryInterface::class);

        $service = new AuthService($userRepository);

        $data = new RegisterData(
            name: 'John Doe',
            email: 'john@example.com',
            password: 'secret123',
        );

        $user = new User;
        $user->id = 10;
        $user->email = $data->email;

        $userRepository
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($user);

        Auth::shouldReceive('login')
            ->once()
            ->with($user)
            ->andReturn('jwt-token-123');

        $token = $service->register($data);

        $this->assertSame('jwt-token-123', $token);
    }
}
