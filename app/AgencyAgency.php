<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class AgencyAgency extends Model
{
    protected $casts = [
        'info' => 'object',
    ];
    protected $table = Constants::AGENCY_AGENCY_DB;
    protected $fillable = [
        'agency_parent_id', 'agency_agency_category_id', 'agency_id',
        'capacity_percent', 'type_price', 'price', 'percent', 'award',
        'status', 'info'
    ];

    public function agency()
    {
        return $this->hasOne(Agency::class, 'id', 'agency_id')->where('deleted_at', null);
    }

    public function category()
    {
        return $this->hasOne(AgencyAgencyCategory::class, 'id', 'agency_agency_category_id');
    }

    public function user()
    {
        return $this->hasOne(AgencyUser::class, 'agency_id', 'agency_id')
            ->join(Constants::USERS_DB, Constants::USERS_DB . '.id', '=', Constants::AGENCY_USERS_DB . '.user_id')
            ->where(Constants::AGENCY_USERS_DB . '.role', Constants::ROLE_ADMIN);
    }
}