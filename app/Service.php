<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $casts = [
        'info' => 'object',
    ];
    protected $table = Constants::SERVICE_DB;
    protected $fillable = [
        'customer_id', 'service', 'info'
    ];
}