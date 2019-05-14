<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class ApiApp extends Authenticatable
{
    use SoftDeletes;
    protected $table = Constants::API_APP_DB;
    protected $casts = [
        'info' => 'object',
    ];
    protected $fillable = [
        'api_id', 'app_id', 'info', 'created_at'
    ];
    public $timestamps = false;
}
