<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class SupplierSales extends Model
{
    protected $casts = [
        'info' => 'object',
    ];
    protected $table = Constants::SUPPLIER_SALES_DB;
    protected $fillable = [
        'app_id', 'supplier_id', 'sales_id', 'capacity_percent', 'type_price',
        'price', 'percent', 'status', 'info'
    ];
}
