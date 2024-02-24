{{ __('Szia, :name', ['name' => $details['username']]) }}!

{{ __('Valaki elindított egy regisztrációs folyamatot a Destination.hu oldalon.') }}

{{ __('Amennyiben te voltál az, kattints az alábbi linkre a regisztráció véglegesítéséhez') }}:

{{ URL::to('confirm-registration') }}/{{ $details['user_id'] }}/{{ $details['unique_id'] }}
