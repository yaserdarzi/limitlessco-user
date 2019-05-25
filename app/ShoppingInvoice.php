<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class ShoppingInvoice extends Model
{
    protected $casts = [
        'info' => 'object',
    ];
    protected $table = Constants::SHOPPING_INVOICE_DB;
    protected $fillable = [
        'app_id', 'shopping_id', 'customer_id', 'phone', 'name',
        'count_all', 'price_all', 'percent_all', 'code_coupon',
        'type_status', 'status', 'type', 'invoice_status', 'payment_token',
        'ref_id', 'market', 'info',
    ];
}