<?php

declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class InvalidCredentialsException extends HttpException
{
    public function __construct()
    {
        parent::__construct(Response::HTTP_UNAUTHORIZED, 'Неверные логин или пароль');
    }
}
