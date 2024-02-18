@extends('base')

@section('main')

    <div class="container">

        <h1>{{ __('My Places') }}</h1>

        @if(count($places) > 0)
        <table class="table">
            <thead>
            <tr>
                <th class="align-center">#</th>
                <th class="align-left">{{ __('Name') }}</th>
                <th class="align-center">{{ __('Status') }}</th>
                <th colspan="2">{{ __('Actions') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($places as $place)
                <tr>
                    <td class="align-center">{{ $place->id }}</td>
                    <td>{{ $place->name }}</td>
                    <td class="align-center">{!! $place->status === 'active' ? __('Active') : __('Inactive') !!}</td>
                    <td class="align-center"><a href="edit_place/{{ $place->id }}" titl="{{ __('Edit Place') }}"><button type="button" class="edit"><i class="material-icons">edit_location_alt</i> {{ __('Edit Place') }}</button></a></td>
                    <td class="align-center"><a href="/upload_images/{{ $place->id }}" titl="{{ __('Edit Images') }}"><button type="button" class="edit"><i class="material-icons">image</i> {{ __('Edit Images') }}</button></a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @else
            <div class="message warning">{{ __('You don\'t have any places yet.') }} <a href="create-place" title="{{ __('Submit your first place.') }}">{{ __('Submit your first place.') }}</a></div>
        @endif
    </div>

@endsection
