<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class AgencyAgencyCategoryCommission extends Model
{
    protected $table = Constants::AGENCY_AGENCY_CATEGORY_COMMISSION_DB;
    protected $casts = [
        'info' => 'object',
    ];
    protected $fillable = [
        'agency_agency_category_id', 'shopping_id', 'type', 'percent', 'price', 'award',
        'income', 'is_price_power_up', 'info'
    ];
}
