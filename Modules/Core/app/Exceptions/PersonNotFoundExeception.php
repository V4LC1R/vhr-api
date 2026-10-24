<?php

namespace Modules\Core\Exceptions;

use App\Exceptions\DomainException;

class PersonNotFoundExeception extends DomainException
{
    public function __construct(
        string $message = 'Pessoa Não encontrada.',
        int $code = 204,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
