<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrderOnline extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'mysql';
    protected $table = 'sales_orders';
    protected $guarded = [];

    public function deliveryService()
    {
        return $this->belongsTo(DeliveryService::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function orderedBy()
    {
        return $this->belongsTo(User::class, 'ordered_by_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by_id');
    }

    public function onlineShopProvider()
    {
        return $this->belongsTo(OnlineShopProvider::class);
    }

    public function detailSalesOrders()
    {
        return $this->hasMany(DetailSalesOrder::class, 'sales_order_id');
    }
}
