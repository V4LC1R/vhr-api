<?php

use App\Contracts\CompanyRepositoryInterface;
use App\Contracts\DailyEngagementRepositoryInterface;
use App\Contracts\EmployeeRepositoryInterface;
use App\Contracts\EmploymentRepositoryInterface;
use App\Contracts\PersonRepositoryInterface;
use App\Contracts\TimeEntryRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Contracts\WorkloadRepositoryInterface;
use Modules\Core\Models\UserCompany;

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

if (! function_exists('employmentRepo')) {
    function employmentRepo(): EmploymentRepositoryInterface
    {
        return app(EmploymentRepositoryInterface::class);
    }
}

if (! function_exists('workloadRepo')) {
    function workloadRepo(): WorkloadRepositoryInterface
    {
        return app(WorkloadRepositoryInterface::class);
    }
}

if (! function_exists('timeEntryRepo')) {
    function timeEntryRepo(): TimeEntryRepositoryInterface
    {
        return app(TimeEntryRepositoryInterface::class);
    }
}

if (! function_exists('dailyEngagementRepo')) {
    function dailyEngagementRepo(): DailyEngagementRepositoryInterface
    {
        return app(DailyEngagementRepositoryInterface::class);
    }
}

if (! function_exists('currentCompany')) {
    function currentCompany(): ?UserCompany
    {
        return app()->bound('currentCompany')
            ? app('currentCompany')
            : null;
    }
}
