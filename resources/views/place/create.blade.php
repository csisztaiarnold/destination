@extends('base')

@section('main')

    <div class="general-form container">

        <h1>{{ __('Hozz létre egy helyet') }}</h1>

        @if($errors->any())
            <div class="message error">{{ $errors->first() }}</div>
        @endif

        @if(Session::get('message'))
            <div class="message success">{!! Session::get('message') !!}</div>
        @endif

        @if(Session::get('warning'))
            <div class="message warning">{!! Session::get('warning') !!}</div>
        @endif

        <form name="insert" id="insert" method="post" action="{{ route('store') }}">
            @csrf
            <div class="field-group">
                <label for="name">{{ __('Név') }}:</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" class="text" required/>
            </div>

            <div class="row">

                <div class="field-group col-lg-6">
                    <label for="parent-category">{{ __('Kategória') }}:</label>
                    <select id="parent-category" name="category_id" required></select>
                </div>

                <div class="field-group col-lg-6">
                    <label for="category">{{ __('Alkategória') }}:</label>
                    <select id="category" name="subcategory_id" required></select>
                </div>

            </div>

            <div class="row">

                <div class="field-group col-lg-6">
                    <label for="county">{{ __('Megye') }}:</label>
                    <select id="county" name="county_id" required></select>
                </div>

                <div class="field-group col-lg-6">
                    <label for="city">{{ __('Város') }}:</label>
                    <select id="city" name="city_id" required></select>
                </div>

            </div>

            <div class="row">

                <div class="field-group col-lg-6">
                    <label for="latitude">{{ __('Földrajzi szélesség (latitude)') }}:</label>
                    <input type="text" id="latitude" name="latitude" value="{{ old('latitude') }}" class="text" required/>
                </div>

                <div class="field-group col-lg-6">
                    <label for="longitude">{{ __('Földrajzi hosszúság (longitude)') }}:</label>
                    <input type="text" id="longitude" name="longitude" value="{{ old('longitude') }}" class="text" required/>
                </div>

            </div>

            <div class="field-group">
                <label for="tags">{{ __('Tagek') }}:</label>
                <input type="text" name="tags" id="tags" value="{{ old('tags') }}" class="text" />
            </div>

            <div class="field-group">
                <label for="address">{{ __('Cím') }}:</label>
                <input type="text" name="address" id="address" value="{{ old('address') }}" class="text" />
            </div>

            <div class="field-group">
                <label for="facebook_page">{{ __('Facebook oldal') }}:</label>
                <input type="text" name="facebook_page" id="facebook_page" value="{{ old('facebook_page') }}" class="text" />
            </div>

            <div class="field-group">
                <label for="website">{{ __('Honlap') }}:</label>
                <input type="text" name="website" id="website" value="{{ old('website') }}" class="text" />
            </div>

            <div class="row">

                <div class="field-group col-lg-4">
                    <input type="checkbox" name="accessible_with_car" id="accessible_with_car" class="checkbox" value="1" @if (old('accessible_with_car')) checked @endif/>
                    <label for="accessible_with_car">{{ __('Megközelíthető autóval') }}</label>
                </div>

                <div class="field-group col-lg-4">
                    <input type="checkbox" name="disabled_accessible" id="disabled_accessible" class="checkbox" value="1" @if(old('disabled_accessible')) checked @endif/>
                    <label for="disabled_accessible">{{ __('Mozgáskorlátozott-barát') }}</label>
                </div>

                <div class="field-group col-lg-4">
                    <input type="checkbox" id="kid_friendly" name="kid_friendly" class="checkbox" value="1" @if(old('kid_friendly')) checked @endif/>
                    <label for="kid_friendly">{{ __('Gyermekbarát') }}</label>
                </div>

            </div>

            <div class="field-group">
                <label for="description">{{ __('Leírás') }}:</label>
                <textarea name="description" id="description">{{ old('description') }}</textarea>
            </div>

            <input type="submit" class="button button-primary" value="{{ __('Mentés') }}"/>

        </form>

    </div>

    <script>
        $(function () {
            populateDropdown('#parent-category', 0, 0, '{{ url('category') }}/');
            populateDropdown('#category', 1, 0, '{{ url('category') }}/');
            $(document).on('change', '#parent-category', function () {
                populateDropdown('#category', $(this).val(), 0, '{{ url('category') }}/');
            });
            populateDropdown('#county', 0, 0, '{{ url('locality') }}/');
            populateDropdown('#city', 6, 0, '{{ url('locality') }}/');
            $(document).on('change', '#county', function () {
                populateDropdown('#city', $(this).val(), 0, '{{ url('locality') }}/');
            });
        });
    </script>
@endsection
