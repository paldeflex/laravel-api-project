<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\RegisterData;
use App\Models\User;
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
