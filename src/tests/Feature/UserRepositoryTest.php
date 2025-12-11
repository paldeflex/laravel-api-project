<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\DTO\RegisterData;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @throws BindingResolutionException
     */
    public function test_create_creates_user_with_hashed_password(): void
    {
        /** @var UserRepositoryInterface $repository */
        $repository = $this->app->make(UserRepositoryInterface::class);

        $data = new RegisterData(
            name: 'John Doe',
            email: 'john@example.com',
            password: 'secret123',
        );

        $user = $repository->create($data);

        $this->assertNotNull($user->id);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);

        $this->assertTrue(Hash::check('secret123', $user->password));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'john@example.com',
        ]);
    }
}
