@php $image = \App\Models\Image::mainImage($place->id); @endphp

<div class="place-item">

    <a href="{{ route('show', [Str::slug($place->name), $place->id]) }}" title="{{ $place->name }}" class="title">
        <div class="inner-cut">{{ $place->name }}</div>
        @include('place.star-rating', ['single_place' => $place])
    </a>

    <a href="{{ route('show', [Str::slug($place->name), $place->id]) }}" title="{{ $place->name }}" class="place-item-image" style="background-image: url('@if( isset($image->filename)){{ URL::asset('item_images/' . $place->id . '/' . $image->filename . '_' . $image->id . '_mthumb.' . $image->extension ) }}@endif');"></a>

    @if($place->city_name)
        <div class="city-county">
            <a href="{{ route('create_search_query') }}/{{ Str::slug($place->city_name) }}/city/{{ $place->city_id }}" title="{{ __('Keresés') }}: {{ $place->city_name }}">{{ $place->city_name }}</a>,
            <a href="{{ route('create_search_query') }}/{{ Str::slug($place->county_name) }}/county/{{ $place->county_id }}" title="{{ __('Keresés') }}: {{ $place->county_name }}">{{ $place->county_name }}</a>
        </div>
    @endif

    @if(isset($nearby_places) && $nearby_places === true)
        @php
            $distance = haversineGreatCircleDistance($single_place->latitude, $single_place->longitude, $place->latitude, $place->longitude);
            $unit = 'km';
            if($distance < 1) {
                $distance = $distance*1000;
                $unit = 'm';
            }
        @endphp
    @endif

    @if(isset($single_place))
    <div class="distance-between">
        <a href="{{ URL::to('between') }}/{{ $single_place->id }},{{ $place->id }}" title="{!! __('Körülbelül ilyen messze van: <strong>:distance:unit</strong>', ['distance' => $distance, 'unit' => $unit]) !!}">{!! __('Körülbelül ilyen messze van: <strong>:distance:unit</strong>', ['distance' => $distance, 'unit' => $unit]) !!}</a>
    </div>
    @endif

    <div class="description">

        <div class="content-wrapper">{{ Str::limit($place->description, 160, '…') }}</div>

        @if($from_route !== true)
        <div class="noselect add-to-route add-to-route-{{ $place->id }} {!! in_array($place->id, $my_route_array) ? 'active' : '' !!} {!! $route_length_limit <= count($my_route_array) && !in_array($place->id, $my_route_array) ? 'disabled' : '' !!}" data-id="{{ $place->id }}">
            {!! in_array($place->id, $my_route_array) ? __('Ki az útitervből') : __('Útitervbe veszem') !!}
        </div>
        @else
            <div class="reorder-container">
                @if(isset($destination_count) && $destination_count > 0)
                    <a href="{{ URL::to('set-as-starting-point') . '/' . $route_id . '/' . $place->id }}" title="{{ __('Állítsd be kiindulópontnak') }}" class="noselect reorder reorder-up"> {{ __('Állítsd be kiindulópontnak') }}</a>
                @endif
                @if(isset($places) && count($places) !== $destination_count+1)
                    <a href="{{ URL::to('set-as-ending-point') . '/' . $route_id . '/' . $place->id }}" title="{{ __('Állítsd be végpontnak') }}" class="noselect reorder reorder-down"> {{ __('Állítsd be végpontnak') }}</a>
                @endif
            </div>
        @endif

    </div>

</div>
