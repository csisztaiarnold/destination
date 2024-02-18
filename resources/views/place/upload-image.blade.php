@extends('base')

@section('main')

    <div class="edit-images container">

        <h1>{{ __('Edit Images') }} {{ __('for') }} <a href="/show/place/{{ $place->id }}" title="{{ __('Edit') }} {{ $place->name }}">{{ $place->name }}</a></h1>

        <div class="edit-images-place-link">
            <a href="/edit_place/{{ $place->id }}" title="{{ __('Edit Place') }}"><button type="button" class="edit"><i class="material-icons">edit_location_alt</i> {{ __('Edit Place') }}</button></a>
        </div>

        <div class="remaining-images">{{ __('Remaining images') }}: <strong>{{ $image_upload_limit-$number_of_uploaded_images }}</strong></div>

        <section class="image-upload">

            @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($image_upload_limit-$number_of_uploaded_images > 0)
                {{ Form::open(['url' => 'save_image', 'method' => 'post', 'files' => TRUE, 'id' => 'image-upload-form']) }}

                <div class="form-group">
                    <label for="image">{{ __('Upload') }}:</label>
                    <small>{{ __('Allowed formats: JPG/JPEG, GIF, PNG. Max. size: 10MB') }}</small>
                    <input type="file" name="image" id="image" class="form-control" required="required"/>
                    <input type="hidden" name="place_id" value="{{ $place->id }}"/>
                </div>

                {{ Form::close() }}
            @endif

        </section>

        @if(count($uploaded_images) > 0)
            <section class="uploaded-images general-form">
                <div class="image-container-wrapper">
                    @foreach($uploaded_images as $uploaded_image)
                        <div class="image-container">
                            <img
                                src="{{ URL::asset('item_images/' . $uploaded_image->place_id . '/' . $uploaded_image->filename . '_' . $uploaded_image->id . '_thumb.' . $uploaded_image->extension ) }}"/>
                            <a href="{{ URL::to('delete_image/' . $uploaded_image->id) }}" title="{{ __('Delete') }}" onclick="return confirm('Biztos vagy benne, hogy törlöd a képet?')" class="image-delete">&times;</a>
                            @if($uploaded_image->row_order === 1)
                                <div class="image-order">{{ __('Main image') }}</div>
                            @else
                                <div class="image-order">{{ __('Order') }}: {{ $uploaded_image->row_order }}</div>
                            @endif
                            @if($max_order !== 1)
                                <div class="image-reorder">
                                    @if($uploaded_image->row_order !== 1)<a href="{{ URL::to('reorder_image/up/' . $uploaded_image->id ) }}" title="{{ __('Reorder: up') }}">▲</a>@endif
                                    @if($uploaded_image->row_order !== $max_order)<a href="{{ URL::to('reorder_image/down/' . $uploaded_image->id ) }}" title="{{ __('Reorder: down') }}">▼</a>@endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

    </div>

    <script>
        $(document).ready(function () {
            $(document).on('change', '#image', function () {
                $('#image-upload-form').submit();
            });
        });
    </script>

@endsection
