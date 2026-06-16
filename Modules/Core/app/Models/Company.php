<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Database\Factories\CompanyFactory;
use Modules\Core\Http\Resources\CompanyResource;

#[Fillable([
    'name',
    'cnpj',
])]
#[UseFactory(CompanyFactory::class)]
#[UseResource(CompanyResource::class)]
class Company extends Model
{
    use HasUuids;
    use HasFactory;
    use SoftDeletes;

    protected $table = 'core.companies';

    public $incrementing = false;

    protected $keyType = 'string';
}
