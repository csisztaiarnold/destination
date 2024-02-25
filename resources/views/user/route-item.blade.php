<div class="saved-route">
    <h2><a href="{{ URL::to('show-route') }}/{{ $route->slug }}/{{ $route->id }}" title="{{ $route->route_name }}">{{ $route->route_name }}</a></h2>
    @php
        $posts = \App\Models\Place::where('status', 'active')
            ->whereIn('id', explode(',', $route->best_route))
            ->orderByRaw("FIELD(id, $route->best_route)")
            ->get();
        $post_string = '';
    @endphp
    <div class="saved-route-destination-list">
        @foreach($posts as $post)
            @php $post_string .= $post->name . ' ➜ ' @endphp
        @endforeach
        {!! substr($post_string, 0, -4) !!}
    </div>
    <a href="{{ URL::to('show-route') }}/{{ $route->slug }}/{{ $route->id }}" title="{{ $route->route_name }}" class="set-public"><span class="material-icons">visibility</span> Megnézem</a>
    @if((int) $route->public === 1)
        <a href="{{ URL::to('update_route/set_private') }}/{{ $route->id }}" title="{{ __('Tedd priváttá (csak te láthatod)') }}" class="set-private"><span class="material-icons">unpublished</span> {{ __('Tedd priváttá (csak te láthatod)') }}</a>
    @else
        <a href="{{ URL::to('update_route/set_public') }}/{{ $route->id }}" title="{{ __('Tedd nyilvánossá (bárki láthatja)') }}" class="set-public"><span class="material-icons">check_circle</span> {{ __('Tedd nyilvánossá (bárki láthatja)') }}</a>
    @endif
    <a href="{{ URL::to('delete_saved_route') }}/{{ $route->id }}" title="{{ __('Töröld az útitervet') }}" class="delete-route" onclick="return confirm('Biztos vagy benne?')"><span class="material-icons">delete</span> {{ __('Töröld az útitervet') }}</a>
    <br />
</div>