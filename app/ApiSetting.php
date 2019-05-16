<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class ApiSetting extends Model
{
    protected $table = Constants::API_SETTING_DB;
    protected $casts = [
        'info_payment' => 'object',
        'info_sms' => 'object',
        'info' => 'object',
    ];
    protected $fillable = [
        'api_id', 'type_payment', 'info_payment',
        'type_sms', 'info_sms', 'info',
    ];
}
