<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductOnlineMapping extends Model
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $table = 'product_online_mappings';
    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function onlineShopProvider()
    {
        return $this->belongsTo(OnlineShopProvider::class);
    }
}
