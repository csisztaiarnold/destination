@extends('base')

@section('main')

    @if(isset($single_place->id))

        @php
            $image = \App\Models\Image::mainImage($single_place->id);
        @endphp

        <div class="header"
             style="background-image: url('@if( isset($image->filename)){{ URL::asset('item_images/' . $single_place->id . '/' . $image->filename . '_' . $image->id . '.' . $image->extension ) }}@endif');">

            <div class="container">

                <div class="title-container">

                    <div class="title">

                        @if(Auth::id() === $single_place->user_id || (isset(Auth::user()->role) && Auth::user()->role === 'admin'))
                            <div class="edit-place"><a href="/edit_place/{{ $single_place->id }}"><i
                                            class="material-icons">edit_location_alt</i> {{ __('Edit place') }}</a>
                            </div>
                        @endif

                        <h1>{{ $single_place->name }}</h1>

                        <div class="details">

                            <div>
                                @if($single_place->city_name)

                                    <div class="city-county">
                                        <a href="{{ route('create_search_query') }}/{{ Str::slug($single_place->city_name) }}/city/{{ $single_place->city_id }}"
                                           title="{{ __('Search in') }}: {{ $single_place->city_name }}">{{ $single_place->city_name }}</a>,
                                        <a href="{{ route('create_search_query') }}/{{ Str::slug($single_place->county_name) }}/county/{{ $single_place->county_id }}"
                                           title="{{ __('Search in') }}: {{ $single_place->county_name }}">{{ $single_place->county_name }}</a>
                                    </div>

                                @endif

                                @if($single_place->address)
                                    <div class="address"><span
                                                class="material-icons">business</span> {{ $single_place->address }}
                                    </div>
                                @endif

                                @if($single_place->facebook_page)
                                    <div class="facebook-page">
                                        <a href="{{ $single_place->facebook_page }}"
                                           title="{{ __('Facebook page') }}"><span
                                                    class="material-icons">facebook</span> {{ __('Facebook page') }}</a>
                                    </div>
                                @endif

                                @if($single_place->website)
                                    <div class="website">
                                        <a href="{{ $single_place->website }}" title="{{ __('Website') }}"><span
                                                    class="material-icons">language</span> {{ __('Website') }}</a>
                                    </div>
                                @endif
                            </div>
                            <div>
                                @if($single_place->accessible_with_car)
                                    <div class="accessibility-info"><span
                                                class="material-icons">directions_car</span> {{ __('Megközelíthető autóval') }}
                                    </div>
                                @endif
                                @if($single_place->disabled_accessible)
                                    <div class="accessibility-info"><span
                                                class="material-icons">accessible</span> {{ __('Mozgáskorlátozott-barát') }}
                                    </div>
                                @endif
                                @if($single_place->kid_friendly)
                                    <div class="accessibility-info"><span
                                                class="material-icons">escalator_warning</span> {{ __('Gyermekbarát') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        @include('place.star-rating', ['single_place' => $single_place])

                        <div class="add-to-route-container">
                            <div class="noselect add-to-route add-to-route-{{ $single_place->id }} {!! in_array($single_place->id, $my_route_array) ? 'active' : '' !!} {!! $route_length_limit <= count($my_route_array) && !in_array($single_place->id, $my_route_array) ? 'disabled' : '' !!}"
                                 data-id="{{ $single_place->id }}">
                                {!! in_array($single_place->id, $my_route_array) ? __('Ki az útitervből') : __('Útitervbe veszem') !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="container">

            <div class="row">

                <div class="col-lg-12">
                    @if(count($uploaded_images) > 0)
                        <div class="uploaded-images-container maximages" data-maximages="{{ count($uploaded_images) }}">
                            @php $image_n = 0 @endphp
                            @foreach($uploaded_images as $uploaded_image)
                                <div class="image imgn_{{ $image_n }}"
                                     style="background-image:url({{ URL::asset('item_images/' . $single_place->id . '/' . $uploaded_image->filename . '_' . $uploaded_image->id . '_mthumb.' . $uploaded_image->extension ) }})"
                                     data-fullsize="{{ URL::asset('item_images/' . $single_place->id . '/' . $uploaded_image->filename . '_' . $uploaded_image->id . '.' . $uploaded_image->extension ) }}"
                                     data-imgn="{{ $image_n }}"></div>
                                @php $image_n++ @endphp
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>

            <div class="row">

                <div class="col-lg-8 place-details">

                    <div class="description">
                        {!! nl2br(convertTextToLink($single_place->description)) !!}
                    </div>

                    <div class="map-wrapper">

                        <a href="https://www.google.com/maps/search/{{ $single_place->latitude }},{{ $single_place->longitude}}"
                           class="google-maps-route place-page" target="_blank"><span
                                    class="material-icons">place</span> {{ __('See on Google Maps') }}</a>

                        <div id="map" class="map"></div>
                    </div>
                    <script>
                        var markerIcon = L.icon({
                            iconUrl: iconurl_not_in_route,
                            iconSize: iconsize,
                            iconAnchor: iconanchor,
                            popupAnchor: popupanchor
                        });
                        var map = L.map('map');
                        L.control.scale().addTo(map);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '© OpenStreetMap contributors'
                        }).addTo(map);
                        map.setView([{{ $single_place->latitude }}, {{ $single_place->longitude }}], 16);
                        L.marker([{{ $single_place->latitude }}, {{ $single_place->longitude }}], {icon: markerIcon}).addTo(map);
                    </script>

                </div>

                <div class="col-lg-4">

                    <div class="included-in-routes">
                        @if(isset($routes) && count($routes) > 0)
                            <div class="route-list">
                                <h2>{{ __('Ez a hely a következő útiterv(ek)ben van benne') }}:</h2>
                                @foreach($routes as $route)
                                    <a href="{{ URL::to('show-route') }}/{{ $route->slug }}/{{ $route->id }}"><span
                                                class="material-icons">map</span> {{ $route->name }}
                                        ({{ __(':stop_no állomás', ['stop_no' => count(explode(',',$route->route))]) }})</a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            <div class="nearby-places item-list">
                @if(count($nearby_places) > 0)
                    <h2>{{ __('Nearby places') }}</h2>
                    <div class="row">
                        @foreach($nearby_places as $place)
                            <div class="col-lg-6">
                                @include('place.item-card', [
                                    'from_route' => false,
                                    'nearby_places' => true,
                                    'single_place' => $single_place,
                                ])
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

        @if(Auth::id())
            <script>
                $(document).ready(function () {

                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $(document).on('mouseover', '.vote', function () {
                        var vote_element = $('.vote');
                        vote_element.removeClass('voting-mouseover-finished');
                        var vote = $(this).data('vote');
                        vote_element.each(function (i, obj) {
                            if (vote >= $(this).data('vote')) {
                                $(this).addClass('voting-mouseover');
                            } else {
                                $(this).removeClass('voting-mouseover');
                            }
                        });
                    });

                    $(document).on('mouseout', '.rating', function () {
                        $('.vote').removeClass('voting-mouseover');
                    });

                    $(document).on('click', '.vote', function () {
                        var vote = $(this).data('vote');
                        $.ajax({
                            url: "{{ URL::to('/store_vote') }}",
                            type: "POST",
                            dataType: "json",
                            data: {
                                vote: $(this).data('vote'),
                                place_id: {{ $single_place->id }},
                            },
                            context: this,
                            complete: function () {
                                var vote_notification = $('.done');
                                vote_notification.hide();
                                vote_notification.fadeIn().delay(2000).fadeOut();
                                $('.vote').removeClass('active');
                                $('.vote').each(function (i, obj) {
                                    var this_em = $(this);
                                    if (vote >= this_em.data('vote')) {
                                        this_em.addClass('active');
                                    }
                                });
                            }
                        });
                    });
                });

            </script>
        @endif

    @endif

@endsection