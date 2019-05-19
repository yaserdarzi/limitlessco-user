<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class App extends Model
{
    use SoftDeletes;
    protected $table = Constants::APP_DB;
    protected $casts = [
        'info' => 'object',
    ];
    protected $fillable = [
        'title', 'app', 'country', 'is_supplier', 'is_agency',
        'is_api', 'info'
    ];
    protected $dates = ['deleted_at'];
}
