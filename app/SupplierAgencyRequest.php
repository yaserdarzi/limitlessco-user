<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class SupplierAgencyRequest extends Model
{
    protected $casts = [
        'info' => 'object',
    ];
    protected $table = Constants::SUPPLIER_AGENCY_REQUEST_DB;
    protected $fillable = [
        'supplier_id', 'name', 'phone', 'email', 'city', 'fax',
        'web', 'address', 'status', 'info'
    ];
}