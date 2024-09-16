<?php

namespace App\Models;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Route extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'route',
        'best_route',
        'custom_start_coordinates',
        'name',
    ];

    /**
     * @var int
     */
    const route_length_limit = 16;

    /**
     * Return max number of routes
     *
     * @return int
     */
    public static function getRouteLenghtLimit(): int
    {
        return self::route_length_limit;
    }

    /**
     * Returns a route array if the my_route session exits.
     *
     * @return array
     */
    public static function getRouteArray(): array
    {
        return request()->session()->get('my_route') === null ? [] : request()->session()->get('my_route');
    }

    /**
     * Implode lat/lng data and send the request URL to the OSRM router.
     *
     * @param array $places
     * @return mixed
     * @throws GuzzleException
     */
    public function queryOsrmForWaypoints(array $places)
    {
        // TODO: the OSRM demo site supports driving only, change this to 'foot' (IIRC) once the OSRM server is set up locally
        $url = 'https://router.project-osrm.org/trip/v1/driving/';
        foreach ($places as $place) {
            if (isset($place->longitude, $place->latitude)) {
                $url .= $place->longitude . ',' . $place->latitude . ';';
            }
        }
        $url = substr($url, 0, -1) . '?source=first&destination=last';

        $res = (new \GuzzleHttp\Client)->request('GET', $url);
        $json = $res->getBody();

        return json_decode($json);
    }

    /**
     * Get all public routes where the location is included
     *
     * @param $id
     *
     * @return array
     */
    public function getPublicRoutesWithLocation(int $id)
    {
        return DB::select(
            DB::raw('SELECT `id`, `name`, `route`, `slug` FROM `routes` WHERE FIND_IN_SET(' . $id . ',best_route)')
        );
    }

    /**
     * Return a single rout based on the path
     *
     * @param string $path
     * @param        $custom_start_coordinates
     *
     * @return mixed
     */
    public function getSingleRouteByPath(string $path, $custom_start_coordinates)
    {
        return self::where('route', $path)
            ->when($custom_start_coordinates, function ($query, $custom_start_coordinates) {
                return $query->where('custom_start_coordinates', $custom_start_coordinates);
            })
            ->first();
    }

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
        $route = self::where('id', $route_id)
            ->where('user_id', $user_id)
            ->first();
        if (isset($route->id)) {
            return true;
        }
        return false;
    }
}
