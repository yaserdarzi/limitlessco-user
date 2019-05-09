<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class UsersRefer extends Model
{
    protected $table = Constants::USERS_REFER_DB;
    protected $fillable = [
        'user_id', 'ref_user_id', 'app_id', 'type', 'created_at'
    ];

    public $timestamps = false;
}
