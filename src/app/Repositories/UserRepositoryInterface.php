<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTO\RegisterData;
use App\Models\User;

interface UserRepositoryInterface
{
    public function create(RegisterData $data): User;
}
