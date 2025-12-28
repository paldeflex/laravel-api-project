<?php

declare(strict_types=1);

namespace App\Repositories\Contracts\Users;

use App\DTO\Auth\RegisterData;
use App\Models\User;

interface UserRepositoryInterface
{
    public function create(RegisterData $data): User;
}
