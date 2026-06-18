<?php

namespace App\Providers;

use App\Contracts\CompanyRepositoryInterface;
use App\Contracts\PersonRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Repositories\CompanyRepository;
use Modules\Core\Repositories\PersonRepository;
use Modules\Core\Repositories\UserRepository;

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
    }
}
