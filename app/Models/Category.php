<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Category extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * The subcategory relation.
     *
     * @return HasMany
     */
    public function subcategory(): HasMany
    {
        return $this->hasMany('App\Category', 'parent_id');
    }

    public function places()
    {
        return $this->belongsToMany('App\Models\Places');
    }

    /**
     * Return categories based on parent ID.
     *
     * @param int $parent_id
     * @return Collection
     */
    public function getCategoriesWithPlaceCount(int $parent_id): Collection
    {
        return $this->where('parent_id', $parent_id)
            ->select([
                '*',
                DB::raw(
                    '(SELECT COUNT(*) FROM `category_places` WHERE `category_places`.`category_id` = `categories`.`id`) as `number_of_places`'
                ),
            ])
            ->orderBy('row_order', 'asc')
            ->get();
    }

    /**
     * Return categories based on an ID (categories) array.
     *
     * @param array $id_array
     * @return Collection
     */
    public static function getCategoriesBasedOnIdArray(array $id_array): Collection
    {
        return self::whereIn('id', $id_array)
            ->orderBy('name', 'asc')
            ->get();
    }
}
