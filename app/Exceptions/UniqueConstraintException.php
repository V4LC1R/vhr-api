<?php

namespace App\Exceptions;

class UniqueConstraintException extends DomainException
{
    public function __construct(
        string $message = 'Registro já cadastrado.',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
