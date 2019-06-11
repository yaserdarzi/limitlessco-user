<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class Crm extends Model
{
    protected $casts = [
        'info' => 'object',
    ];
    protected $table = Constants::CRM_DB;
    protected $fillable = [
        'user_id', 'role', 'status', 'info'
    ];
}
