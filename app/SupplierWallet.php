<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class SupplierWallet extends Model
{
    protected $table = Constants::SUPPLIER_WALLET_DB;
    protected $fillable = [
        'supplier_id', 'price'
    ];
}
