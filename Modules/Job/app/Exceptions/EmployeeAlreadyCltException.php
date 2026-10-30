<?php

namespace Modules\Job\Exceptions;

use App\Exceptions\DomainException;

class EmployeeAlreadyCltException extends DomainException
{
    public function __construct(
        string $message = 'Esse funcionario já possui vinculo CLT ativo.',
        int $code = 409,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
