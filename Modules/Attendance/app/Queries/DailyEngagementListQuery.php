<?php

namespace Modules\Attendance\Queries;

use Modules\Attendance\Filters\EmployeeNameFilter;
use Modules\Attendance\Filters\MonthFilter;
use Modules\Attendance\Models\DailyEngagement;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DailyEngagementListQuery
{
    public static function make(string $companyId): QueryBuilder
    {
        return QueryBuilder::for(DailyEngagement::class)
            ->where('companyId', $companyId)
            ->with(['employee.person', 'timeEntries', 'approvedByUserCompany.person'])
            ->allowedFilters(
                AllowedFilter::exact('employeeId'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('type'),
                AllowedFilter::custom('month', new MonthFilter()),
                AllowedFilter::custom('employeeName', new EmployeeNameFilter()),
                AllowedFilter::exact('date'),
            )
            ->allowedSorts('date')
            ->defaultSort('-date');
    }
}
