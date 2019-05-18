<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class AgencyUser extends Model
{
    protected $casts = [
        'info' => 'object',
    ];
    protected $table = Constants::AGENCY_USERS_DB;
    protected $fillable = [
        'user_id', 'agency_id', 'type', 'percent', 'price',
        'award', 'income', 'role', 'info'
    ];
}
