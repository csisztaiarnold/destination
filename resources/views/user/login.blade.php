@extends('base')

@section('main')

<div class="login-form">

    <h1>{{ __('Login') }}</h1>

    @if($errors->any())
        <div class="message error">{{ $errors->first() }}</div>
    @endif

    @if(Session::get('message'))
        <div class="message success">{!! Session::get('message') !!}</div>
    @endif

    @if(Session::get('warning'))
        <div class="message warning">{!! Session::get('warning') !!}</div>
    @endif

    <form name="authenticate" id="authenticate" method="post" action="{{ route('authenticate') }}">
        @csrf
        <div class="field-group">
            <label for="email">{{ __('Email') }}:</label>
            <input type="email" name="email" id="email" value="{{ isset($email) ? $email : old('email') }}" class="text" placeholder="{{ __('Email') }}" required />
        </div>
        <div class="field-group">
            <label for="password">{{ __('Password') }}:</label>
            <input type="{{ isset($tmp_password) ? 'text' : 'password'}}" name="password" id="password" class="text" value="{{ $tmp_password ?? '' }}" placeholder="{{ __('Password') }}" required />
        </div>

        @if(request()->getPathInfo() === "/login/from_route")
        <input type="hidden" name="redirect_to_route" value="{{  request()->session()->get('last_route_page') }}" />
        @endif

        @if(isset($tmp_password))<input type="hidden" name="from_password_reset" value="1" />@endif

        <input type="submit" class="button button-primary" value="{{ __('Login') }}" />
    </form>

    <div class="forgot-your-password"><a href="{{ route('reset_password') }}" title="{{ __('Forgot your password?') }}">{{ __('Forgot your password?') }}</a></div>

</div>

@endsection
