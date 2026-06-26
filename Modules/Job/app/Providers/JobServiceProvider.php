<?php

namespace Modules\Job\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Gate;
use Modules\Job\Models\Employee;
use Modules\Job\Models\Workload;
use Modules\Job\Policies\EmployeePolicy;
use Modules\Job\Policies\WorkloadPolicy;

class JobServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Job';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'job';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();
         Gate::policy(Employee::class, EmployeePolicy::class);
         Gate::policy(Workload::class, WorkloadPolicy::class);
    }

    /**
     * Define module schedules.
     *
     * @param $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
