@extends('base')

@section('main')
    <h1>{{ __('Mentett útiterveim') }}</h1>
    <div class="container route-list-container">
    @if(count($routes) > 0)
        @foreach($routes as $route)
             @include('user.route-item')
        @endforeach
            @else
        <div class="alert alert-warning">
            {{ __('Még nincs egy mentett útiterved sem.') }}
        </div>
    @endif
    </div>
@endsection