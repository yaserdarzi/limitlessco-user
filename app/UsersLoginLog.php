<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class UsersLoginLog extends Model
{
    protected $table = Constants::USERS_LOGIN_LOG_DB;
    protected $fillable = ['user_id', 'ip_address', 'type', 'status'];
}