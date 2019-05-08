<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $table = Constants::WALLET_DB;
    protected $fillable = [
        'user_id', 'price'
    ];
}
