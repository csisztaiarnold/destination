<header>
    <div class="container">
        <a href="{{ url('/') }}" title="{{ __('Home') }}"><img src="{{ asset('img/logo-destination.svg') }}" alt="Logo" class="logo" /></a>
        <nav>
            <ul class="mainmenu">
                @if(Auth::id())
                    @if(Auth::user()->role === 'admin' || Auth::user()->role === 'editor')
                    <li><a href="{{ route('create_place') }}" title="{{ __('Submit a place') }}"><i class="material-icons">add</i> {{ __('Submit') }}</a></li>
                    <li><a href="{{ route('my_places') }}" title="{{ __('My places') }}"><i class="material-icons">person_pin_circle</i> {{ __('My places') }}</a></li>
                    @endif
                    <li><a href="{{ route('my_routes') }}" title="{{ __('Mentett útiterveim') }}"><i class="material-icons">route</i> {{ __('Mentett útiterveim') }}</a></li>
                    <li><a href="{{ route('logout') }}" title="{{ __('Kilépés') }}"><i class="material-icons">logout</i> {{ __('Kilépés') }}</a></li>
                @else
                    <li>
                        <a href="{{ route('register') }}" title="{{ __('Regisztráció') }}"><i class="material-icons">person_add</i> {{ __('Regisztráció') }}</a>
                    </li>
                    <li>
                        <a href="{{ route('login') }}" title="{{ __('Bejelentkezés') }}"><i class="material-icons">login</i> {{ __('Bejelentkezés') }}</a>
                    </li>
                @endif
            </ul>
        </nav>
        <div class="menu-open-close"><i class="material-icons">menu</i></div>
    </div>
    <a href="{{ route('my_route') }}" title="{{ __('Útiterv') }}" class="my-route {!! count(\App\Models\Route::getRouteArray()) < 2 ? 'hidden' : '' !!}">{{ __('Útiterv') }} (<span class="route-length">{{ count(\App\Models\Route::getRouteArray()) }}</span>)</a>
</header>
