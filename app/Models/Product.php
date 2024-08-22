<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function parentCategory() {
        return $this->belongsTo(Category::class, 'parent_category_id');
    }

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function varients() {
        return $this->hasMany(ProductVarientValue::class);
    }
}
