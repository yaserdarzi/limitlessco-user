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
        'customer_id', 'name', 'phone', 'count_all', 'price_all',
        'percent_all', 'income','income_all', 'income_you',
        'price_payment', 'code_coupon', 'type_status', 'status', 'type',
        'invoice_status', 'payment_token', 'ref_id', 'market', 'info',
    ];
}