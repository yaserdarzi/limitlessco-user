<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Api extends Authenticatable
{
    use SoftDeletes;
    protected $table = Constants::API_DB;
    protected $casts = [
        'info' => 'object',
    ];
    protected $fillable = [
        'name', 'type', 'percent', 'price', 'income', 'award',
        'info'
    ];
    protected $dates = ['deleted_at'];
}
