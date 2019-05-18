<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class SupplierUser extends Model
{
    protected $casts = [
        'info' => 'object',
    ];
    protected $table = Constants::SUPPLIER_USERS_DB;
    protected $fillable = [
        'user_id', 'supplier_id', 'type', 'percent', 'price',
        'award', 'income', 'role', 'info'
    ];
}
