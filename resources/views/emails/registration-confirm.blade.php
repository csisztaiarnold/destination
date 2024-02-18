<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Please confirm your registration') }}</title>
</head>
<body>
    <h1>{{ __('Hello, :name', ['name' => $details['username']]) }}!</h1>
    {{ __('Someone (probably you) used this email address to create an account at :site', ['site' => URL::to('/')]) }}<br />
    <br />
    {{ __('If it was you who tried to register, please confirm your action by visiting this link') }}:<br />
    <br />
    <a href="{{ URL::to('confirm-registration') }}/{{ $details['user_id'] }}/{{ $details['unique_id'] }}" title="{{ __('Confirm your registration') }}">{{ URL::to('confirm-registration') }}/{{ $details['user_id'] }}/{{ $details['unique_id'] }}</a><br />
</body>
</html>
