<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Roles extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'roles';

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('withoutSuperAdmin', function (Builder $builder) {
            $builder->where('id', '!=', 1);
        });
    }
}
