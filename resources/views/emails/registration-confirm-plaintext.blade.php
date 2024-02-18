{{ __('Hello, :name', ['name' => $details['username']]) }}!

{{ __('Someone (probably you) used this email address to create an account at :site.', ['site' => URL::to('/')]) }}

{{ __('If it was you who tried to create an account, please confirm your action by visiting this link') }}:

{{ URL::to('confirm-registration') }}/{{ $details['user_id'] }}/{{ $details['unique_id'] }}
