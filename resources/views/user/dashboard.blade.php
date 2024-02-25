@extends('base')

@section('main')

    <div class="dashboard container">

        <h1>{{ __('Irányítópult') }}</h1>

        <div class="dashboard-inner-wrapper">

            <div class="dashboard-item">

                <h2>{{ __('Jelszófrissítés') }}</h2>

                @if($errors->any())
                    <div class="message error">{{ $errors->first() }}</div>
                @endif

                @if(Session::get('message'))
                    <div class="message success">{!! Session::get('message') !!}</div>
                @endif

                @if(Session::get('warning'))
                    <div class="message warning">{!! Session::get('warning') !!}</div>
                @endif

                @if($from_password_reset === true)
                    <div class="message warning">{!! __('<strong>Kérjük változtasd meg a jelszavadat minél előbb!</strong>') !!}</div>
                @endif

                <form name="update-password" id="update-password" method="post" action="{{ url('update_password') }}">
                    @csrf
                    <div class="field-group">
                        <label for="old_password">{{ __('Régi jelszó') }}:</label>
                        <input type="{{ $from_password_reset === true ? 'text' : 'password' }}" name="old_password" id="old_password" class="text" value="{{ $from_password_reset === true ? $tmp_password : '' }}" required />
                    </div>
                    <div class="field-group">
                        <label for="password">{{ __('Új jelszó') }}:</label>
                        <input type="password" name="password" id="password" class="text" required />
                    </div>
                    <div class="field-group">
                        <label for="password_confirm">{{ __('Jelszó ismét') }}:</label>
                        <input type="password" name="password_confirm" id="password_confirm" class="text" required />
                    </div>
                    <div class="field-group">
                        <input type="submit" name="submit" id="submit" class="button button-primary" value="{{ __('Frissítés') }}" />
                    </div>
                </form>
            </div>

            <div class="dashboard-item">
                <h2>{{ __('Legutóbbi mentett útitervek') }}</h2>

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

        </div>

    </div>
@endsection
