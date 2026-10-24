<?php

namespace Modules\Core\Exceptions;

use App\Exceptions\DomainException;

class PersonUpdateException extends DomainException
{
    public function __construct(
        string $message = 'Erro ao tentar atulizar pessoa.',
        int $code = 422,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
