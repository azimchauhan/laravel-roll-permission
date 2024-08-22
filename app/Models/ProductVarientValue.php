<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVarientValue extends Model
{
    use HasFactory, SoftDeletes;

    public function categoryVarient() {
        return $this->belongsTo(CategoryVarient::class);
    }
}
