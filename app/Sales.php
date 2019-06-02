<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sales extends Model
{
    use SoftDeletes;
    protected $table = Constants::SALES_DB;
    protected $casts = [
        'info' => 'object',
    ];
    protected $fillable = [
        'title', 'desc', 'logo', 'type', 'count_sellers', 'info'
    ];

    protected $dates = ['deleted_at'];
}
