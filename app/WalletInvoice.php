<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class WalletInvoice extends Model
{
    protected $casts = [
        'info' => 'object',
    ];
    protected $table = Constants::WALLET_INVOICE_DB;
    protected $fillable = [
        'user_id', 'wallet_id', 'price_before', 'price',
        'price_after', 'price_all', 'type_status', 'status',
        'type', 'invoice_status', 'payment_token',
        'ref_id', 'market', 'info'
    ];
}
