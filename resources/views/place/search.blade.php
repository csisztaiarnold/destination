@extends('base')

@section('main')

    <div class="map-wrapper">
        <div id="map" class="map"></div>
    </div>

    <div class="title-wrapper">
        <h1>{!! $page_title !!}</h1>
    </div>

    @php
        $my_route_array = \App\Models\Route::getRouteArray();
        $route_length_limit = \App\Models\Route::getRouteLenghtLimit();
    @endphp

    @include('place.map-js') {{-- Places on the map come from the PlaceController ($markers) --}}

    @include('place.filter')

    @if(count($places) > 0)

        <div class="pagination-wrapper">{{ $places->links("pagination::bootstrap-4") }}</div>

        <div class="item-list container row">

            @foreach($places as $place)

                <div class="col-lg-6">
                    @include('place.item-card', ['from_route' => false])
                </div>

            @endforeach

        </div>

        <div class="pagination-wrapper">{{ $places->links("pagination::bootstrap-4") }}</div>

    @else
        <div class="no-results">{{ __('Sajnos nincs találat') }}</div>
    @endif

@endsection
