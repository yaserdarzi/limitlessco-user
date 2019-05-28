<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class SupplierAgencyCategory extends Model
{
    protected $casts = [
        'info' => 'object',
    ];
    protected $table = Constants::SUPPLIER_AGENCY_CATEGORY_DB;
    protected $fillable = [
        'supplier_id', 'title', 'type_price', 'price', 'percent', 'info'
    ];
}