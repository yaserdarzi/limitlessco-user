<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class ApiWallet extends Model
{
    protected $table = Constants::API_WALLET_DB;
    protected $fillable = [
        'api_id', 'price'
    ];
}
