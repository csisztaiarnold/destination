@extends('base')

@section('main')

    <div class="general-form container">

        <h1>{{ __('Edit place') }}: <a href="/show/place/{{ $place->id }}" title="{{ $place->name }}">{{ $place->name }}</a></h1>

        <div class="edit-images-place-link">
            <a href="/upload_images/{{ $place->id }}" title="{{ __('Edit Images') }}"><button type="button" class="edit"><i class="material-icons">image</i> {{ __('Edit Images') }}</button></a>
        </div>

        @if($place->status !== 'active')
            <div class="message warning">{{ __('This place is not active yet.') }}</div>
        @endif

        @if($errors->any())
            <div class="message error">{{ $errors->first() }}</div>
        @endif

        @if(Session::get('message'))
            <div class="message success">{!! Session::get('message') !!}</div>
        @endif

        @if(Session::get('warning'))
            <div class="message warning">{!! Session::get('warning') !!}</div>
        @endif

        <form name="update_place" id="update_place" method="post" action="{{ route('update_place') }}">
            @csrf
            <div class="field-group">
                <label for="name">{{ __('Name') }}:</label>
                <input type="text" name="name" id="name" value="{!! old('name') !== '' ? $place->name : old('name') !!}" class="text" required/>
            </div>

            <div class="row">

                <div class="field-group col-lg-6">
                    <label for="parent-category">{{ __('Category') }}:</label>
                    <select id="parent-category" name="category_id" required>

                    </select>
                </div>

                <div class="field-group col-lg-6">
                    <label for="category">{{ __('Subcategory') }}:</label>
                    <select id="category" name="subcategory_id" required></select>
                </div>

            </div>

            <div class="row">

                <div class="field-group col-lg-6">
                    <label for="county">{{ __('County') }}:</label>
                    <select id="county" name="county_id" required></select>
                </div>

                <div class="field-group col-lg-6">
                    <label for="city">{{ __('City') }}:</label>
                    <select id="city" name="city_id" required></select>
                </div>

            </div>

            <div class="row">

                <div class="field-group col-lg-6">
                    <label for="latitude">{{ __('Latitude') }}:</label>
                    <input type="text" id="latitude" name="latitude" value="{!! old('latitude') !== '' ? $place->latitude : old('latitude') !!}" class="text" required/>
                </div>

                <div class="field-group col-lg-6">
                    <label for="longitude">{{ __('Longitude') }}:</label>
                    <input type="text" id="longitude" name="longitude" value="{!! old('longitude') !== ''? $place->longitude : old('longitude') !!}" class="text" required/>
                </div>

            </div>

            <div class="field-group">
                <label for="tags">{{ __('Tags') }}:</label>
                <input type="text" name="tags" id="tags" value="{!! old('tags') !== '' ? $place->tags : old('tags') !!}" class="text" />
            </div>

            <div class="field-group">
                <label for="address">{{ __('Address') }}:</label>
                <input type="text" name="address" id="address" value="{!! old('address') !== '' ? $place->address : old('address') !!}" class="text"/>
            </div>

            <div class="field-group">
                <label for="facebook_page">{{ __('Facebook page') }}:</label>
                <input type="text" name="facebook_page" id="facebook_page" value="{!! old('facebook_page') !== '' ? $place->facebook_page : old('facebook_page') !!}" class="text"/>
            </div>

            <div class="field-group">
                <label for="website">{{ __('Website') }}:</label>
                <input type="text" name="website" id="website" value="{!! old('website') !== '' ? $place->website : old('website') !!}" class="text"/>
            </div>

            <div class="row">

                <div class="field-group col-lg-4">
                    <input type="checkbox" name="accessible_with_car" id="accessible_with_car" class="checkbox" value="1" @if(old('accessible_with_car')) checked @elseif ($place->accessible_with_car) checked @endif />
                    <label for="accessible_with_car">{{ __('Megközelíthető autóval') }}</label>
                </div>

                <div class="field-group col-lg-4">
                    <input type="checkbox" name="disabled_accessible" id="disabled_accessible" class="checkbox" value="1" @if(old('disabled_accessible')) checked @elseif ($place->disabled_accessible) checked @endif />
                    <label for="disabled_accessible">{{ __('Mozgáskorlátozott-barát') }}</label>
                </div>

                <div class="field-group col-lg-4">
                    <input type="checkbox" id="kid_friendly" name="kid_friendly" class="checkbox" value="1" @if(old('kid_friendly')) checked @elseif ($place->kid_friendly) checked @endif />
                    <label for="kid_friendly">{{ __('Gyermekbarát') }}</label>
                </div>

            </div>

            <div class="field-group">
                <label for="description">{{ __('Description') }}:</label>
                <textarea name="description" id="description">{!! old('description') !== '' ? $place->description : old('description') !!}</textarea>
            </div>

            @if(Auth::user()->role === 'admin')
                <div class="field-group">
                    <input type="checkbox" id="status" name="status" class="checkbox" value="1" @if(old('status') === '1') checked @elseif ($place->status === 'active') checked @endif />
                    <label for="status">{{ __('Active') }}</label>
                </div>
           @endif

            <input type="hidden" value="{{ $place->id }}" name="place_id" />

            <input type="submit" class="button button-primary" value="{{ __('Submit') }}"/>

        </form>

    </div>

    <script>
        $(function () {
            populateDropdown('#parent-category', 0, {{ $category_id }}, '{{ url('category') }}/');
            populateDropdown('#category', {{ $category_id }}, {{ $subcategory_id }}, '{{ url('category') }}/');
            $(document).on('change', '#parent-category', function () {
                populateDropdown('#category', $(this).val(), 0, '{{ url('category') }}/');
            });
            populateDropdown('#county', 0, {{ $place->county_id }}, '{{ url('locality') }}/');
            populateDropdown('#city', {{ $place->county_id }}, {{ $place->city_id }}, '{{ url('locality') }}/');
            $(document).on('change', '#county', function () {
                populateDropdown('#city', $(this).val(), 0, '{{ url('locality') }}/');
            });
        });
    </script>
@endsection
