<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class Route
 *
 * @package App
 */
class Vote extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'place_id',
        'vote',
    ];

    /**
     * Return user vote on post
     *
     * @param $place_id
     * @param $user_id
     *
     * @return |null
     */
    public function getUserVote($place_id, $user_id)
    {
        $vote = self::where('post_id', $place_id)
            ->where('user_id', $user_id)
            ->first();
        return $vote->vote ?? null;
    }

    /**
     * Calculate weighted mean of votes for a post
     *
     * @param $place_id
     *
     * @return float|int
     */
    public function calculateWeightedMean($place_id)
    {
        $votes = DB::select(
            "SELECT SUM(vote = 1) AS '1', SUM(vote = 2) AS '2', SUM(vote = 3) as '3', SUM(vote = 4) as '4', SUM(vote = 5) as '5' FROM votes where `place_id` = '$place_id'"
        );

        if (count((array)$votes[0]) === 0) {
            return 0;
        }

        $total_weight = 0;
        $total_reviews = 0;

        foreach ((array)$votes[0] as $weight => $number_of_reviews) {
            $weight_multiplied_by_number = $weight * $number_of_reviews;
            $total_weight += $weight_multiplied_by_number;
            $total_reviews += $number_of_reviews;
        }

        return $total_weight / $total_reviews;
    }

}
