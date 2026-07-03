<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailSalesOrder extends Model
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $table = 'detail_sales_orders';
    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function salesOrderOnline()
    {
        return $this->belongsTo(SalesOrderOnline::class, 'sales_order_id', 'id');
    }
}
