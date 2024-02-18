<script>
    const availableTags = ['tag1', 'tag2', 'tag3'];

    // Icons for the markers on maps.
    const iconurl_in_route = '{{ asset('css/vendor/images/route.svg') }}';
    const iconurl_not_in_route = '{{ asset('css/vendor/images/unvisited.svg') }}';
    const iconurl_visited = '{{ asset('css/vendor/images/visited.svg') }}';
    const iconurl_you_are_here = '{{ asset('css/vendor/images/you-are-here.svg') }}';
    const iconsize = [30, 30];
    const iconanchor = [15, 15];
    const popupanchor = [-1, -4];
    const add_destination_to_route = '{{ __('Válassz ki célállomásokat az útitervedhez!') }}';
    const one_more_destination_to_route = '{{ __('Válassz ki legalább két célállomást, hogy elkészítsűk az útitervedet') }}';
    let routeArray = [];
    @if(count(\App\Models\Route::getRouteArray()) > 0)
        routeArray = [
        {!! implode(',', \App\Models\Route::getRouteArray()) !!}
    ]
    @endif

    // Clicking on an `.add-to-route` element.
    $(function () {
        $(document).on('click', '.add-to-route', function () {
            const selectedElement = $(this);
            if (routeArray.length <= 15 || selectedElement.hasClass('active')) {
                addToRoute(selectedElement.data('id'), '{{ route('add_place_to_route') }}', '{{ __('Útitervbe veszem') }}', '{{ __('Ki az útitervből') }}', 16);
            }
        });
    });
</script>
