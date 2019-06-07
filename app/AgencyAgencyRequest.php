<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class AgencyAgencyRequest extends Model
{
    protected $casts = [
        'info' => 'object',
    ];
    protected $table = Constants::AGENCY_AGENCY_REQUEST_DB;
    protected $fillable = [
        'agency_id', 'name', 'phone', 'email', 'city', 'fax',
        'web', 'address', 'status', 'info'
    ];
}