<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class ShoppingBag extends Model
{
    protected $casts = [
        'shopping' => 'object',
    ];
    protected $table = Constants::SHOPPING_BAG_DB;
    protected $fillable = [
        'shopping_id', 'customer_id', 'title', 'title_more',
        'date', 'date_end', 'start_hours', 'end_hours', 'price_fee',
        'percent_fee', 'count', 'price_all', 'percent_all',
        'income', 'income_all', 'income_you', 'shopping'
    ];
}