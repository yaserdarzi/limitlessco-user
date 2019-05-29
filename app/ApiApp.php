<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Foundation\Auth\User as Authenticatable;

class ApiApp extends Authenticatable
{
    protected $table = Constants::API_APP_DB;
    protected $casts = [
        'info' => 'object',
    ];
    protected $fillable = [
        'api_id', 'app_id', 'info', 'created_at'
    ];
    public $timestamps = false;
}
