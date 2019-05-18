<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class AgencyApi extends Model
{
    protected $casts = [
        'info' => 'object',
    ];
    protected $table = Constants::AGENCY_API_DB;
    protected $fillable = [
        'agency_id', 'app_id', 'info'
    ];
}