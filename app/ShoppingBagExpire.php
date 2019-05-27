<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class ShoppingBagExpire extends Model
{
    protected $casts = [
        'shopping' => 'object',
    ];
    protected $table = Constants::SHOPPING_BAG_EXPIRE_DB;
    protected $fillable = [
        'customer_id', 'expire_time', 'status'
    ];
}