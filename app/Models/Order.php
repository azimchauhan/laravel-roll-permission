<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function inventory() {
        return $this->belongsTo(Inventory::class);
    }

    public function products() {
        return $this->hasMany(OrderProduct::class);
    }
}
