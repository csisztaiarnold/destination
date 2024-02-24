<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Jelszó megváltoztatása') }}</title>
</head>
<body>
    <h1>{{ __('Szia :name', ['name' => $details['username']]) }}!</h1>
    {{ __('Valaki elindított egy jelszóváltoztatási folyamatot a Destination.hu oldalon.') }}<br />
    <br />
    {{ __('Amennyiben te szeretnéd megváltoztatni a jelszavát a Destination.hu oldalon, kattints a következő linkre') }}:<br />
    <br />
    <a href="{{ URL::to('confirm-password-reset') }}/{{ $details['user_id'] }}/{{ $details['unique_id'] }}" title="{{ __('Jelszó változtatása') }}">{{ URL::to('confirm-password-reset') }}/{{ $details['user_id'] }}/{{ $details['unique_id'] }}</a><br />
    <br />
    <strong>{{ __('Ezt link csak egyszer használható!') }}</strong>
</body>
</html>
