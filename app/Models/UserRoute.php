<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRoute extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'route_id',
        'name',
        'public',
    ];

}
