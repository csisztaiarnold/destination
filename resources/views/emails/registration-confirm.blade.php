<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('A regisztráció megerősítése') }}</title>
</head>
<body>
    <h1>{{ __('Szia, :name', ['name' => $details['username']]) }}!</h1>
    {{ __('Valaki elindított egy regisztrációs folyamatot a Destination.hu oldalon.') }}<br />
    <br />
    {{ __('Amennyiben te voltál az, kattints az alábbi linkre a regisztráció véglegesítéséhez') }}:<br />
    <br />
    <a href="{{ URL::to('confirm-registration') }}/{{ $details['user_id'] }}/{{ $details['unique_id'] }}" title="{{ __('Confirm your registration') }}">{{ URL::to('confirm-registration') }}/{{ $details['user_id'] }}/{{ $details['unique_id'] }}</a><br />
</body>
</html>
