<script>

var locations = [];

{{-- $my_route_array is passed from views/place/search.blade.php --}}
@if(count($markers) > 0)
    locations = [
    @foreach($markers as $marker)
        @php $image = \App\Models\Image::mainImage($marker->id); @endphp
        [
        `
        <div class="marker-item marker-item-{{ $marker->id }}">
            <a href="{{ route('show', [Str::slug($marker->name), $marker->id]) }}" title="{{ $marker->name }}" class="title">{{ $marker->name }}</a><br />
            @if(isset($image->filename))
            <a href="{{ route('show', [Str::slug($marker->name), $marker->id]) }}" title="{{ $marker->name }}"><img src="{{ URL::asset('item_images/' . $marker->id . '/' . $image->filename . '_' . $image->id . '_mthumb.' . $image->extension ) }}" width="150" /></a>
            @endif
            <div class="add-to-route-container">
                <div class="add-to-route add-to-route-{{ $marker->id }} {{ in_array($marker->id, $my_route_array) ? 'active' : '' }}" data-id="{{ $marker->id }}">
                    {!! in_array($marker->id, $my_route_array) ? __('Ki az útitervből') : __('Útitervbe veszeme') !!}
                </div>
            </div>
        </div>
        `,
        {{ $marker->latitude }},
        {{ $marker->longitude }},
        {{ $marker->id }},
        ],
    @endforeach
    ];
@endif

const map = L.map('map');
L.control.scale().addTo(map);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

var arrayOfMarkers = []; // This array will be used to set bounds on the map.
for (let i = 0; i < locations.length; i++) {
    let markerIcon = L.icon({
        iconUrl: iconurl_not_in_route,
        iconSize: iconsize,
        iconAnchor: iconanchor,
        popupAnchor: popupanchor
    });
    if(routeArray.includes(locations[i][3])) {
        markerIcon = L.icon({iconUrl: iconurl_in_route, iconSize: iconsize, iconAnchor: iconanchor, popupAnchor: popupanchor});
    }
    const marker = new L.marker([locations[i][1], locations[i][2]], {markerId: locations[i][3], icon: markerIcon})
        .bindPopup(locations[i][0])
        .addTo(map)
        .on('click', function () {
            // Click resets to the initial state of the marker, so in case a route was added or removed from the
            // list, this won't be reproduced in the marker. Get the `active` class from the `.place-item` element.
            if ((this.options.icon.options.iconUrl).includes("route.svg") === true) {
                $('.add-to-route-' + this.options.markerId).addClass('active');
                $('.add-to-route-' + this.options.markerId).text('{{ __('Ki az útitervből') }}');
            } else {
                $('.add-to-route-' + this.options.markerId).removeClass('active');
                $('.add-to-route-' + this.options.markerId).text('{{ __('Útitervbe veszem') }}');
            }
        });
    arrayOfMarkers.push([locations[i][1], locations[i][2]]);
}

// If the location array is empty, only show a hardcoded marker.
if(locations.length > 0) {
    var bounds = new L.LatLngBounds(arrayOfMarkers);
    map.fitBounds(bounds);
} else {
    L.marker([47.1766598, 19.5423386]).addTo(map).bindPopup("Magyarország közepe").openPopup();
    map.setView([47.1766598, 19.5423386], 12);
}

@if(count(\App\Models\Route::getRouteArray()) === 1)
    $('.route-add-notification').text(one_more_destination_to_route);
@endif

@if(count(\App\Models\Route::getRouteArray()) < 1)
    $('.route-add-notification').text(add_destination_to_route);
@endif

@if(count(\App\Models\Route::getRouteArray()) > 1)
    $('.route-add-notification').hide();
@endif

@if(Request::segment(1) === 'list-places')
$( document ).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        url: '/route_number',
        type: 'POST',
        dataType: 'text',
        complete: function (data) {
            if(data.responseText > 0) {
                $('.route-add-notification').hide();
            } else {
                $('.route-add-notification').show();
            }
        }
    });
});
@endif
</script>
