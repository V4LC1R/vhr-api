<?php

namespace Modules\Attendance\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Gate;
use Modules\Attendance\Models\DailyEngagement;
use Modules\Attendance\Models\TimeEntry;
use Modules\Attendance\Policies\DailyEngagementPolicy;
use Modules\Attendance\Policies\TimeEntryPolicy;

class AttendanceServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Attendance';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'attendance';

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
        Gate::policy(TimeEntry::class, TimeEntryPolicy::class);
        Gate::policy(DailyEngagement::class, DailyEngagementPolicy::class);
    }
}
