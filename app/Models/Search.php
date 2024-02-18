<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Search extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'search_requests',
        'slug',
    ];

    /**
     * Return the search queries object based on ID.
     *
     * @param int $id
     * @return mixed
     */
    public function getSearchQuery(int $id)
    {
        return json_decode(self::where('id', $id)->first()->search_requests);
    }

}
