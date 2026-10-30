<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class DomainException extends Exception
{
    public function __construct(
        string $message,
        public readonly int $httpStatus = 422,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
