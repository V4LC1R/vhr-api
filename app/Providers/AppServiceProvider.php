<?php

namespace App\Providers;

use App\Contracts\CompanyRepositoryInterface;
use App\Contracts\DailyEngagementRepositoryInterface;
use App\Contracts\EmployeeRepositoryInterface;
use App\Contracts\EmploymentRepositoryInterface;
use App\Contracts\PersonRepositoryInterface;
use App\Contracts\TimeEntryRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Contracts\WorkloadRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use Modules\Attendance\Repositories\DailyEngagementRepository;
use Modules\Attendance\Repositories\TimeEntryRepository;
use Modules\Core\Repositories\CompanyRepository;
use Modules\Core\Repositories\PersonRepository;
use Modules\Core\Repositories\UserRepository;
use Modules\Job\Repositories\EmployeeRepository;
use Modules\Job\Repositories\EmploymentRepository;
use Modules\Job\Repositories\WorkloadRepository;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(CompanyRepositoryInterface::class, CompanyRepository::class);
        $this->app->bind(PersonRepositoryInterface::class, PersonRepository::class);
        $this->app->bind(EmployeeRepositoryInterface::class, EmployeeRepository::class);
        $this->app->bind(EmploymentRepositoryInterface::class, EmploymentRepository::class);
        $this->app->bind(WorkloadRepositoryInterface::class, WorkloadRepository::class);
        $this->app->bind(TimeEntryRepositoryInterface::class, TimeEntryRepository::class);
        $this->app->bind(DailyEngagementRepositoryInterface::class, DailyEngagementRepository::class);
    }
}
