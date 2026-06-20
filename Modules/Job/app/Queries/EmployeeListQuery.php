<?php

namespace Modules\Job\Queries;

use Modules\Job\Models\Employee;
use Modules\Job\Filters\RegisterAtFilter;
use Modules\Job\Filters\EmployeePersonNameFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class EmployeeListQuery
{
    public static function make(): QueryBuilder
    {
        return QueryBuilder::for(Employee::class)
            ->with([
                'person',
                'workload',
            ])
            ->allowedFilters(
                AllowedFilter::custom(
                    'name',
                    new EmployeePersonNameFilter()
                ),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('role'),
                AllowedFilter::custom(
                    'registerAt',
                    new RegisterAtFilter()
                ),
            )
            ->allowedSorts(
                'registerNumber',
                'register_at',
                'status',
                'role',
            )
            ->defaultSort('-registerNumber');
    }
}
