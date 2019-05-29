<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class ApiUser extends Model
{
    protected $casts = [
        'info' => 'object',
    ];
    protected $table = Constants::API_USERS_DB;
    protected $fillable = [
        'user_id', 'api_id', 'role', 'info'
    ];
}
