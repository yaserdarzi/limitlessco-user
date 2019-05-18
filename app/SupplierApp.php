<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class SupplierApp extends Model
{
    protected $casts = [
        'info' => 'object',
    ];
    protected $table = Constants::SUPPLIER_APP_DB;
    protected $fillable = [
        'supplier_id', 'app_id', 'info'
    ];
}
