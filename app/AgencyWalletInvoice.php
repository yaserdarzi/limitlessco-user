<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class AgencyWalletInvoice extends Model
{
    protected $casts = [
        'info' => 'object',
    ];
    protected $table = Constants::AGENCY_WALLET_INVOICE_DB;
    protected $fillable = [
        'agency_id', 'wallet_id', 'price_before', 'price',
        'price_after', 'price_all', 'type_status', 'status',
        'type', 'invoice_status', 'payment_token',
        'ref_id', 'market', 'info'
    ];
}
