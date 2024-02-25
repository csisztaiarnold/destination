{{ __('Valaki elindított egy jelszóváltoztatási folyamatot a Destination.hu oldalon.') }}

{{ __('Amennyiben te vagy az, aki megszeretné változtatni a jelszavát a Destination.hu oldalon, kattints a következő linkre') }}:

{{ URL::to('confirm-password-reset') }}/{{ $details['user_id'] }}/{{ $details['unique_id'] }}