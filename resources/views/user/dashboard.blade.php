@extends('base')

@section('main')

    <div class="dashboard container">

        <h1>{{ __('Dashboard') }}</h1>

        <h2>{{ __('Update your password') }}</h2>

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
            <div class="message warning">{!! __('<strong>Please change your password as soon as possible!</strong>') !!}</div>
        @endif

        <form name="update-password" id="update-password" method="post" action="{{ url('update_password') }}">
            @csrf
            <div class="field-group">
                <label for="old_password">{{ __('Your old password') }}:</label>
                <input type="{{ $from_password_reset === true ? 'text' : 'password' }}" name="old_password" id="old_password" class="text" value="{{ $from_password_reset === true ? $tmp_password : '' }}" required />
            </div>
            <div class="field-group">
                <label for="password">{{ __('Your new password') }}:</label>
                <input type="password" name="password" id="password" class="text" required />
            </div>
            <div class="field-group">
                <label for="password_confirm">{{ __('Confirm your new password') }}:</label>
                <input type="password" name="password_confirm" id="password_confirm" class="text" required />
            </div>
            <div class="field-group">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="{{ __('Update') }}" />
            </div>
        </form>

    </div>

@endsection
