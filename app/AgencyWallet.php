<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class AgencyWallet extends Model
{
    protected $table = Constants::AGENCY_WALLET_DB;
    protected $fillable = [
        'agency_id', 'price'
    ];
}
