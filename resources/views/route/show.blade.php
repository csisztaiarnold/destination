@extends('base')

@section('main')

    @if(Session::has('message'))
        <div class="message alert">
            {{ Session::get('message') }}
        </div>
    @endif
    
    @if($could_be_displayed === false)
        <div class="common-content container">
            <div class="message warning">{{ __('Sajnos ez az útvonal nem létezik vagy nem publikus.') }}</div>
        </div>
    @else

        @if(count($places) > 0 && empty($error))

            <div class="map-wrapper">

                @php $google_map_count = 1; $google_maps_url = ''; @endphp
                @foreach($places as $place)
                    @php
                        $google_maps_url .= urlencode($place->latitude) . ',' . urlencode($place->longitude) . '/';
                        if($google_map_count > 1) {
                            $google_maps_url .= urlencode($place->title) . ',' . urlencode($place->longitude) . '/';
                        };
                    @endphp
                @endforeach

                <a href="https://www.google.com/maps/dir/{{ substr($google_maps_url, 0, -1) }}/" class="google-maps-route" target="_blank"><span class="material-icons">place</span> {{ __('Nézd meg az útitervet a Google Maps-en') }}</a>

                <div id="map" class="map"></div>
            </div>
            <div id="images"></div>

            <div class="container">

                <div class="route-step-container-top row">
                    <div class="col-lg-3"></div>
                    <div class="col-lg-6">
                        <h1>{!! $route->name !!}</h1>
                        <div class="info">{{ __('Az út hossza kb.') }} <span class="distance"></span> km</div>
                    </div>
                    <div class="col-lg-3"></div>
                </div>

            @php $destination_count = 0; @endphp

                <div class="route-actions">
                    <a href="{{ route('delete_route') }}" title="{{ __('Új utiterv') }}" class="delete-route" onclick="return confirm('{{ __('Biztos vagy benne, hogy új utitervet készítesz?') }}')"><span class="material-icons">replay</span> {{ __('Új útiterv') }}</a>
                    @if(\App\Models\Route::routeAlreadySaved($route->id,Auth::id()) === false)
                        <a href="{{ Auth::id() ? '/#' : '/login/from_route' }}" title="{{ Auth::id() ? __('Mentsd le ezt az útitervet') : __('Mentsd le ezt az útitervet') }}" class="save-route {{ Auth::id() ? 'logged-in' : '' }}"><span class="material-icons">star</span> {{ Auth::id() ? __('Mentsd le ezt az útitervet') : __('Lépj be az útiterv mentéséhez') }}</a>
                        @if(Auth::id())
                            <div class="save-route-form form">
                                <input type="text" class="text" id="route-name" name="route-name" placeholder="{{ __('Útiterv neve') }}" /><br />
                                <br />
                                <input type="checkbox" id="route-public" name="route-public" value="1" checked /> {{ __('Nyilvános') }}<br />
                                <br />
                                <button class="save-route-button"><span class="material-icons">check</span> {{ __('Mentés') }}</button>
                            </div>
                        @endif
                    @endif
                </div>

            @foreach($places as $place)
                <div class="route-step-container row">
                    <div class="col-lg-3"></div>
                    <div class="col-lg-6 route-item-container">
                        @if(count($places) > 2)
                            <div class="delete-item"><a href="{{ URL::to('delete-destination') . '/'. $route->id . '/' . $place->id }}" title="{{ __('Delete destination') }}" class="path-delete" onclick="return confirm('{{ __('Biztos vagy benne, hogy törlöd a megállót az útítervből?') }}')"><i class="material-icons">close</i></a></div>
                        @endif
                        @include('place.item-card', [
                            'from_route' => true,
                            'route_id' => $route->id,
                            'destination_count' => $destination_count
                        ])
                    </div>
                    <div class="col-lg-3"></div>
                </div>
                @if($destination_count+1 !== count($places))
                <div class="route-separator row">
                    <div class="col-lg-3"></div>
                    <div class="col-lg-6 line"></div>
                    <div class="col-lg-3"></div>
                </div>
                @endif
                @php
                    $lat_lng_js_string .= 'L.latLng(' . $place->latitude . ', ' . $place->longitude . '),';
                    $popup_text_js_string .= 'if(i == ' . ($destination_count) . ') { text = "' . $place->name .'"; }';
                    $destination_count++;
                @endphp
            @endforeach
            </div>

            <script>
                var map = L.map('map');
                const options = {profile: "mapbox/walking"};
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);
                var control = L.Routing.control({
                    waypoints: [
                        {{ substr($lat_lng_js_string, 0, -1) }}
                    ],
                    createMarker: function(i, waypoints, nWps) {
                        {!! $popup_text_js_string !!}
                        return L.marker(waypoints.latLng)
                        .setIcon(L.icon({
                            iconUrl: iconurl_in_route,
                            iconSize: iconsize,
                            iconAnchor: iconanchor,
                            popupAnchor: popupanchor
                        }))
                        .bindPopup(text);
                    },
                    routeWhileDragging: true,
                    router: L.Routing.mapbox('{{ env('MAPBOX_API_KEY', false) }}', options),
                }).addTo(map);
                control.on('routesfound', function(e) {
                    var routes = e.routes;
                    var summary = routes[0].summary;
                    // alert distance and time in km and minutes
                    $('.distance').text(Math.round(summary.totalDistance / 1000));
                });

                @if(Auth::id())
                // Save route
                $(function () {
                    $(document).on('click', '.save-route.logged-in', function (e) {
                        e.preventDefault();
                        var save_route = $(this);
                        var save_route_form = $('.save-route-form');
                        if(save_route.hasClass('active')) {
                            save_route_form.hide();
                            save_route.removeClass('active');
                        } else {
                            save_route_form.show();
                            save_route.addClass('active');
                        }
                    });

                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $(document).on('click', '.save-route-button', function () {
                        $.ajax({
                            url: "{{ URL::to('/add_route_to_favorites') }}",
                            type: "POST",
                            dataType: "json",
                            data: {
                                route_id: {{ $route->id }},
                                route_name: $('#route-name').val(),
                                route_public: $('#route-public').is(':checked'),
                            },
                            context: this,
                            complete: function () {
                                $('.save-route.logged-in').hide();
                                $('.save-route-form').html('<div class="route-saved">{{ __('Az útiterv mentve!') }}</div>');
                            }
                        });
                    });
                });
                @endif
            </script>

        @endif

    @endif

@endsection
