<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Commission extends Model
{
    protected $table = Constants::COMMISSION_DB;
    protected $casts = [
        'info' => 'object',
    ];
    protected $fillable = [
        'customer_id', 'shopping_id', 'type', 'percent', 'price', 'award',
        'income', 'is_price_power_up', 'info'
    ];
}
