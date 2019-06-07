<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class AgencyAgencyCategory extends Model
{
    protected $casts = [
        'info' => 'object',
    ];
    protected $table = Constants::AGENCY_AGENCY_CATEGORY_DB;
    protected $fillable = [
        'agency_id', 'title', 'type_price', 'price', 'percent', 'info'
    ];
}