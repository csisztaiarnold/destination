@extends('base')

@section('main')

    <div class="password-reset-form">

        <h1>{{ __('Reset your password') }}</h1>

        @if($errors->any())
            <div class="message error">{{ $errors->first() }}</div>
        @endif

        @if(Session::get('message'))
            <div class="message success">{{ Session::get('message') }}</div>
        @else

        <form name="send-email-reset-password" id="send-email-reset-password" method="post" action="{{ route('send_email_reset_password') }}">
            @csrf
            <div class="field-group">
                <label for="email">{{ __('Email') }}:</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" class="text" required />
            </div>

            <input type="submit" class="button button-primary" value="{{ __('Send an email with password reset link') }}" />
        </form>

        @endif

    </div>

@endsection
