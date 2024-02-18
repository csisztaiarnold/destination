<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@100;200;300;400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="{{ asset('css/vendor/bootstrap-grid.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/vendor/leaflet.css') }}" rel="stylesheet">
    <link href="{{ asset('css/vendor/leaflet-routing-machine.css') }}" rel="stylesheet">
    <link href="{{ asset('css/main.css') }}{{ '?t=' . time() }}" rel="stylesheet">
    <script src="{{ asset('js/vendor/leaflet.js') }}"></script>
    <script src="{{ asset('js/vendor/leaflet-routing-machine.min.js') }}"></script>
    <script src="{{ asset('js/vendor/jquery-3.5.1.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/vendor/jquery-ui.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/main.min.js') }}{{ '?t=' . time() }}" type="text/javascript"></script>
    <script src="{{ asset('js/tags.min.js') }}{{ '?t=' . time() }}" type="text/javascript"></script>
    <script src="{{ asset('js/gallery.min.js') }}{{ '?t=' . time() }}" type="text/javascript"></script>
    @include('js-global-variables')
    <title>{{ $title ?? __('Locality') }}</title>
</head>
<body>
<div class="route-add-notification">
    {{ __('Válassz ki célállomásokat az útitervedhez!') }}
</div>
<!-- Gallery navigation -->
<div class="fullsize-image-container" data-currentimgn="">
    <div class="prev noselect" data-fullsize=""><i class="material-icons">keyboard_arrow_left</i></div>
    <div class="next noselect" data-fullsize=""><i class="material-icons">keyboard_arrow_right</i></div>
    <div class="close-gallery noselect"><i class="material-icons">close</i></div>
</div>
<!-- // Gallery navigation -->
@include('header')

<main class="{{ $page_class ?? 'main' }}">
    @yield('main')
</main>
</body>
</html>
