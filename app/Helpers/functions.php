<?php

use App\Contracts\CompanyRepositoryInterface;
use App\Contracts\EmployeeRepositoryInterface;
use App\Contracts\PersonRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Contracts\WorkloadRepositoryInterface;

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

if (! function_exists('employeeRepo')) {
    function employeeRepo(): EmployeeRepositoryInterface
    {
        return app(EmployeeRepositoryInterface::class);
    }
}

if (! function_exists('workloadRepo')) {
    function workloadRepo(): WorkloadRepositoryInterface
    {
        return app(WorkloadRepositoryInterface::class);
    }
}
