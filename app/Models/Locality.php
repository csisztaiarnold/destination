<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * Class Locality
 * @package App\Models
 */
class Locality extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * The city to county relation.
     *
     * @return HasMany
     */
    public function city(): HasMany
    {
        return $this->hasMany('App\City', 'parent_id');
    }


    /**
     * Return localities based on parent ID.
     *
     * @param int $parent_id
     * @return Collection
     */
    public function getLocalitiesWithPlaceCount(int $parent_id): Collection
    {
        return self::where('parent_id', $parent_id)
            ->select([
                '*',
                DB::raw(
                    '(SELECT COUNT(*) FROM `places` WHERE `places`.`' . ((int)$parent_id === 0 ? 'county_id' : 'city_id') . '` = `localities`.`id` AND `places`.`status` = "active") as `number_of_places`'
                ),
            ])
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Return localities (cities) which have at least one place, based on a parent ID (counties) array.
     *
     * @param array $id_array
     * @return Collection
     */
    public static function getChildLocalitiesWithPlaceCountBasedOnIdArray(array $id_array): Collection
    {
        return self::whereIn('parent_id', $id_array)
            ->select([
                '*',
                DB::raw(
                    '(SELECT COUNT(*) FROM `places` WHERE `city_id`= `localities`.`id` AND `places`.`status` = "active") as `number_of_places`'
                ),
            ])
            ->where(DB::raw('(SELECT COUNT(*) FROM `places` WHERE `city_id`= `localities`.`id`)'), '>', 0)
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Return localities based on an ID (cities) array.
     *
     * @param array $id_array
     * @return Collection
     */
    public static function getLocalitiesBasedOnIdArray(array $id_array): Collection
    {
        return self::whereIn('id', $id_array)
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Return all cities which have at least one place.
     *
     * @return Collection
     */
    public function getAllLocalities(): Collection
    {
        return self::where('parent_id', '!=', 0)
            ->select([
                '*',
                DB::raw('(SELECT COUNT(*) FROM `places` WHERE `city_id`= `localities`.`id`) as `number_of_places`'),
            ])
            ->where(DB::raw('(SELECT COUNT(*) FROM `places` WHERE `city_id`= `localities`.`id`)'), '>', 0)
            ->orderBy('name', 'asc')
            ->get();
    }
}
