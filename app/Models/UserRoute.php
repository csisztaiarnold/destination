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

    /**
     * Is the route already saved?
     *
     * @param $route_id
     * @param $user_id
     *
     * @return bool
     */
    public static function routeAlreadySaved($route_id, $user_id): bool
    {
        $route = self::where('route_id', $route_id)
            ->where('user_id', $user_id)
            ->first();
        if (isset($route->id)) {
            return true;
        }
        return false;
    }
}
