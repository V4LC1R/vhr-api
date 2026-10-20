<?php

use Modules\Attendance\Contracts\DailyEngagementRepositoryInterface;
use Modules\Attendance\Contracts\TimeEntryRepositoryInterface;
use Modules\Core\Contracts\CompanyRepositoryInterface;
use Modules\Core\Contracts\PasswordResetTokenRepositoryInterface;
use Modules\Core\Contracts\PersonRepositoryInterface;
use Modules\Core\Contracts\UserRepositoryInterface;
use Modules\Core\Models\UserCompany;
use Modules\Job\Contract\EmployeeRepositoryInterface;
use Modules\Job\Contract\EmploymentRepositoryInterface;
use Modules\Job\Contract\WorkloadRepositoryInterface;

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

if (! function_exists('passwordResetRepo')) {
    function passwordResetRepo(): PasswordResetTokenRepositoryInterface
    {
        return app(PasswordResetTokenRepositoryInterface::class);
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
