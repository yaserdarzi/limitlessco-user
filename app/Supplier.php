<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;
    protected $casts = [
        'info' => 'object',
    ];
    protected $table = Constants::SUPPLIER_DB;
    protected $fillable = [
        'name', 'image', 'tell', 'type', 'percent', 'price',
        'award', 'income', 'status', 'info'
    ];
}
