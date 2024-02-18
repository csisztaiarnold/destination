<?php

namespace App\Http\Controllers;

use App\Models\Place;
use App\Models\Vote;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    /**
     * @var Place
     */
    private $place;

    /**
     * @var Vote
     */
    private $vote;

    /**
     * @var Request
     */
    private $request;

    /**
     * VoteController constructor.
     *
     * @param Place $place
     * @param Vote $vote
     * @param Request $request
     */
    public function __construct(Place $place, Vote $vote, Request $request)
    {
        $this->place = $place;
        $this->vote = $vote;
        $this->request = $request;
    }

    /**
     * Store vote
     *
     * @return void
     */
    public function storeVote(): void
    {
        // var_dump( $this->request->get('place_id'));
        $this->vote->updateOrCreate(
            ['user_id' => auth()->id(), 'place_id' => $this->request->get('place_id')],
            ['vote' => $this->request->get('vote')]
        );

        $this->place->where('id', $this->request->get('place_id'))
            ->update([
                'rating' => $this->vote->calculateWeightedMean($this->request->get('place_id')),
            ]);
    }

}

