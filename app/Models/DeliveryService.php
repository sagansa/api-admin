<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeliveryService extends Model
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $table = 'delivery_services';
    protected $guarded = [];

    public function salesOrders()
    {
        return $this->hasMany(SalesOrderOnline::class);
    }
}
