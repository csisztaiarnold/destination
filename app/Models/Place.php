<?php

namespace App\Models;

use App\Scopes\StatusScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Place extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'unique_id',
        'user_id',
        'city_id',
        'county_id',
        'sticky',
        'name',
        'description',
        'latitude',
        'longitude',
        'address',
        'website',
        'facebook_page',
        'tags',
        'status',
        'accessible_with_car',
        'disabled_accessible',
        'kid_friendly',
        'rating',
        'row_order',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new StatusScope);
    }

    /**
     * Get the category associated with the place.
     */
    public function category()
    {
        return $this->hasOne('App\Models\CategoryPlace');
    }

    /**
     * Get all of the categories for the place.
     */
    public function categories()
    {
        return $this->belongsToMany('App\Models\Category', 'category_places');
    }

    /**
     * @param string $category_id
     * @param string $county_id
     * @param string $city_id
     * @param string $order
     * @param string $order_direction
     * @param int    $paginate
     * @return mixed
     */
    public function getPlacesBySearchQuery(
        string $category_id,
        string $county_id,
        string $city_id,
        string $order = 'name',
        string $order_direction = 'asc',
        int $paginate
    ) {
        return self::selectRaw(
            '*, (SELECT `name` FROM `localities` WHERE id = places.city_id) as city_name, (SELECT `name` FROM `localities` WHERE id = places.county_id) as county_name'
        )
            ->where('id', '>', 0)
            ->when((!empty($county_id) ? $county_id : null), function ($query, $county_id) {
                return $query->whereIn('county_id', explode(',', $county_id));
            })
            ->when((!empty($city_id) ? $city_id : null), function ($query, $city_id) {
                return $query->whereIn('city_id', explode(',', $city_id));
            })
            ->when((!empty($category_id) ? $category_id : null), function ($query, $category_id) {
                return $query->whereHas('categories', function ($query) use ($category_id) {
                    $query->whereIn('id', explode(',', $category_id));
                });
            })
            ->orderBy($order, $order_direction)
            ->when($paginate, function ($query) use ($paginate) {
                if ($paginate === 1) {
                    return $query->get();
                } else {
                    return $query->paginate($paginate);
                }
            });
    }

    /**
     * Return the lat/lng data from the posts based on the path array
     *
     * @param array $path_array
     *
     * @return array
     */
    public function getLatLangFromPlaces(array $path_array)
    {
        $query = 'SELECT `id`, `latitude`, `longitude` FROM `places` WHERE `status` = "active" AND (';
        $order_by = ') ORDER BY FIELD (id, ';
        foreach ($path_array as $id) {
            $query .= 'id = ' . $id . ' OR ';
            $order_by .= $id . ',';
        }
        return DB::select(substr($query, 0, -4) . substr($order_by, 0, -1) . ')');
    }

    /**
     * Return places by the best route array.
     *
     * @param string $fields              Selected fields.
     * @param array  $best_route_id_array The array of the place IDs sorted in a best route order.
     *
     * @return mixed
     */
    public function getPlacesByBestRouteIdArray(string $fields = '*', array $best_route_id_array)
    {
        return self::selectRaw(
            $fields . ', (SELECT `name` FROM `localities` WHERE id = places.city_id) as city_name, (SELECT `name` FROM `localities` WHERE id = places.county_id) as county_name'
        )
            ->where('status', 'active')
            ->whereIn('id', $best_route_id_array)
            ->orderBy(DB::raw("FIELD(id, " . implode(',', $best_route_id_array) . ")"))
            ->paginate(16);
    }

    /**
     * Creates an array with title strings.
     *
     * @param int $search_id
     * @return array
     */
    public function createTitleArrayBySearchId(int $search_id): array
    {
        $search_request = (new Search)->getSearchQuery($search_id);
        $county_string = '';
        $city_string = '';
        if ($search_request->counties) {
            $counties = Locality::whereIn('id', explode(',', $search_request->counties))->get();
            foreach ($counties as $county) {
                $county_string .= $county->name . ', ';
            }
        }
        if ($search_request->cities) {
            $cities = Locality::whereIn('id', explode(',', $search_request->cities))->get();
            foreach ($cities as $city) {
                $city_string .= $city->name . ', ';
            }
        }
        return [
            'county_string' => substr($county_string, 0, -2),
            'city_string' => substr($city_string, 0, -2),
        ];
    }

    /**
     * Returns nearby places based by distance, latitude and longitude.
     *
     * @param int    $place_id
     * @param int    $distance
     * @param string $latitude
     * @param string $longitude
     *
     * @return mixed
     */
    public function nearbyPlaces(int $place_id, int $distance, string $latitude, string $longitude)
    {
        return self::selectRaw(
            '*, (SELECT `name` FROM `localities` WHERE id = places.city_id) as city_name, (SELECT `name` FROM `localities` WHERE id = places.county_id) as county_name'
        )
            ->where('id', '!=', $place_id)
            ->whereRaw(
                $distance . ' > (6371 * acos(cos(radians(' . $latitude . ')) * cos(radians(`latitude`)) * cos(radians(`longitude`) - radians(' . $longitude . ')) + sin(radians(' . $latitude . ')) * sin(radians(`latitude`)))) AND `status` = "active" ORDER BY (6371 * acos(cos(radians(' . $latitude . ')) * cos(radians(`latitude`)) * cos(radians(`longitude`) - radians(' . $longitude . ')) + sin(radians(' . $latitude . ')) * sin(radians(`latitude`))))'
            )
            ->limit(10)
            ->get();
    }

    /**
     * Get user's image upload limit.
     *
     * @param $user
     * @param $upload_limit
     *
     * @return mixed
     */
    public function getImageUploadLimit($user, $upload_limit)
    {
        $image_upload_limit = $upload_limit;
        if (isset($user->image_upload_limit)) {
            $image_upload_limit = $user->image_upload_limit;
        }
        return $image_upload_limit;
    }

    /**
     * Checks for proper place ownership.
     *
     * @param int $place_id
     * @param int $user_id
     *
     * @return mixed
     */
    public function placeOwnership(int $place_id, int $user_id)
    {
        if (Auth::user()->role !== 'admin') {
            return self::where('id', $place_id)
                ->where('user_id', $user_id)
                ->where('deleted_at', null)
                ->withoutGlobalScope(StatusScope::class)
                ->first();
        } else {
            return self::where('id', $place_id)
                ->withoutGlobalScope(StatusScope::class)
                ->first();
        }
    }
}
