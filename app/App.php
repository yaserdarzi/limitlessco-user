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
        'app', 'type_app', 'type_app_child', 'info', 'cash_back'
    ];
    protected $dates = ['deleted_at'];
}
