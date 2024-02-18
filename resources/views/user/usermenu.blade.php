<nav id="usermenu">
    <ul>
        @if(Auth::id())
            <li><a href="{{ route('dashboard') }}" title="{{ __('Dashboard') }}">{{ __('Dashboard') }}</a></li>
            <li><a href="{{ route('my_places') }}" title="{{ __('My places') }}">{{ __('My places') }}</a></li>
        @endif
    </ul>
</nav>
