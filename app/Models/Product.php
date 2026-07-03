<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'mysql';
    protected $table = 'products';
    protected $guarded = [];

    public function detailSalesOrders()
    {
        return $this->hasMany(DetailSalesOrder::class);
    }
}
