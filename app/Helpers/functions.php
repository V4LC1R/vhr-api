<?php

use App\Contracts\CompanyRepositoryInterface;
use App\Contracts\PersonRepositoryInterface;
use App\Contracts\UserRepositoryInterface;

if (! function_exists('companyRepo')) {
    function companyRepo(): CompanyRepositoryInterface
    {
        return app(CompanyRepositoryInterface::class);
    }
}

if (! function_exists('personRepo')) {
    function personRepo(): PersonRepositoryInterface
    {
        return app(PersonRepositoryInterface::class);
    }
}

if (! function_exists('userRepo')) {
    function userRepo(): UserRepositoryInterface
    {
        return app(UserRepositoryInterface::class);
    }
}
