<?php

namespace Modules\Job\Queries;

use Modules\Job\Filters\EmployeePersonNameFilter;
use Modules\Job\Filters\EmploymentKindFilter;
use Modules\Job\Filters\EmploymentStatusFilter;
use Modules\Job\Filters\RegisterAtFilter;
use Modules\Job\Models\Employee;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class EmployeeListQuery
{
    public static function make(): QueryBuilder
    {
        return QueryBuilder::for(Employee::class)
            ->with(['person', 'activeEmployment.workload'])
            ->allowedFilters(
                AllowedFilter::custom('name', new EmployeePersonNameFilter()),
                AllowedFilter::custom('status', new EmploymentStatusFilter()),
                AllowedFilter::custom('kind', new EmploymentKindFilter()),
                AllowedFilter::custom('registerAt', new RegisterAtFilter()),
            )
            ->allowedSorts('registerNumber', 'registerAt')
            ->defaultSort('-registerNumber');
    }
}
