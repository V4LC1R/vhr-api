<?php

namespace App\Helpers;

use Illuminate\Database\QueryException;

class DatabaseExceptionResolver
{
    public static function isUniqueViolation(QueryException $exception): bool
    {
        return ($exception->errorInfo[0] ?? null) === '23505';
    }
}
