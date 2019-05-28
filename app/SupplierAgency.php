<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class SupplierAgency extends Model
{
    protected $casts = [
        'info' => 'object',
    ];
    protected $table = Constants::SUPPLIER_AGENCY_DB;
    protected $fillable = [
        'supplier_id', 'supplier_agency_category_id', 'agency_id',
        'capacity_percent', 'type_price', 'price', 'percent', 'award',
        'status', 'info'
    ];
}