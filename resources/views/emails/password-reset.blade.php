<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Your password reset link') }}</title>
</head>
<body>
<h1>{{ __('Hello, :name', ['name' => $details['username']]) }}!</h1>
{{ __('Someone (probably you) used this email to request a password reset from :site', ['site' => URL::to('/')]) }}<br />
<br />
{{ __('If it was you who tried to reset your password, please confirm your action by visiting this link') }}:<br />
<br />
<a href="{{ URL::to('confirm-password-reset') }}/{{ $details['user_id'] }}/{{ $details['unique_id'] }}" title="{{ __('Confirm your password reset') }}">{{ URL::to('confirm-password-reset') }}/{{ $details['user_id'] }}/{{ $details['unique_id'] }}</a><br />
<br />
<strong>{{ __('Please note, you will be able to use this link only once.') }}</strong>
</body>
</html>
