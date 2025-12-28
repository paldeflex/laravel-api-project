<?php

declare(strict_types=1);

namespace App\Repositories\Users;

use App\DTO\Auth\RegisterData;
use App\Models\User;
use App\Repositories\Contracts\Users\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

final class UserRepository implements UserRepositoryInterface
{
    public function create(RegisterData $data): User
    {
        return User::create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => Hash::make($data->password),
        ]);
    }
}
