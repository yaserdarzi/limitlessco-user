<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class Shopping extends Model
{
    protected $casts = [
        'shopping' => 'object',
    ];
    protected $table = Constants::SHOPPING_DB;
    protected $fillable = [
        'shopping_id', 'customer_id', 'supplier_id', 'shopping_invoice_id',
        'voucher', 'name', 'phone', 'title', 'title_more', 'date',
        'date_end', 'start_hours', 'end_hours', 'price_fee',
        'percent_fee', 'count', 'price_all', 'percent_all',
        'income_agency', 'income_you', 'price_payment', 'status',
        'shopping'
    ];
}