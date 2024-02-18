@extends('base')

@section('main')

<div class="register-form">

    <h1>{{ __('Regisztráció') }}</h1>

    @if($errors->any())
        <div class="message error">
            <strong>{{ __('Hiba történt...') }}</strong><br /><br />
            @foreach($errors->all() as $error)
                &bull; {{ $error }}<br />
            @endforeach
        </div>
    @endif

    @if(Session::get('message'))
        <div class="message success">{{ Session::get('message') }}</div>
    @else

    <form name="send_registration" id="send_registration" method="post" action="{{ route('send_registration') }}">
        @csrf
        <div class="field-group">
            <label for="username">{{ __('Felhasználónév') }}:</label>
            <div class="info">{{ __('A felhasználónévnek egyedinek kell lennie, és hossza min. 3 karakter lehet.') }}</div>
            <input type="text" name="username" id="username" value="{{ old('username') }}" class="text" required />

        </div>
        <div class="field-group">
            <label for="email">{{ __('Email') }}:</label>
            <input type="text" name="email" id="email" value="{{ old('email') }}" class="text" required />
        </div>
        <div class="field-group">
            <label for="password">{{ __('Jelszó') }}:</label>
            <div class="info">{{ __('A jelszó hossza min. 8 karakter lehet.') }}</div>
            <input type="password" name="password" id="password" class="text" required />
        </div>
        <div class="field-group">
            <label for="password_confirm">{{ __('Jelszó újra') }}:</label>
            <input type="password" name="password_confirm" id="password_confirm" class="text"  required />
        </div>
        <input type="submit" class="button button-primary" value="{{ __('Regisztráció') }}" />
    </form>

    @endif

</div>
@endsection
