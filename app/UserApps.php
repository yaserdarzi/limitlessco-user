<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class UserApps extends Model
{
    protected $table = Constants::USERS_APPS_DB;
    protected $fillable = [
        'user_id', 'app_id', 'activated', 'created_at'
    ];
    public $timestamps = false;
}