<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\CompanyRepositoryInterface;
use App\Contracts\EmployeeRepositoryInterface;
use App\Contracts\PersonRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Contracts\WorkloadRepositoryInterface;
use Modules\Core\Repositories\CompanyRepository;
use Modules\Core\Repositories\PersonRepository;
use Modules\Core\Repositories\UserRepository;
use Modules\Job\Repositories\EmployeeRepository;
use Modules\Job\Repositories\WorkloadRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(CompanyRepositoryInterface::class, CompanyRepository::class);
        $this->app->bind(PersonRepositoryInterface::class, PersonRepository::class);
        $this->app->bind(EmployeeRepositoryInterface::class, EmployeeRepository::class);
        $this->app->bind(WorkloadRepositoryInterface::class, WorkloadRepository::class);
    }
}
