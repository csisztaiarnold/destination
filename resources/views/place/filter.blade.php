<div class="search-filter-container">
    <form name="search-filter" id="search-filter" method="post" action="{{ route('create_search_query') }}">
        @csrf
        <div class="form-group">
            <label>{{ __('Kategóriák') }}:</label>
            <select name="category" id="category">
                <option value="0">{{ __('Összes') }}</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {!! $selected_category === $category->id ? 'selected' : '' !!}>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group subcategory-container inactive">
            <label>{{ __('Alkategóriák') }}:</label>
            <select name="subcategory" id="subcategory">
            </select>
        </div>
        <div class="form-group">
            <label>{{ __('Rendezés') }}:</label>
            <select name="sort_by" id="sort_by">
                <option value="created_at,desc" {!! $sort_by === 'created_at,desc' ? 'selected' : '' !!}>{{ __('Legfrisebb bejegyzések') }}</option>
                <option value="rating,desc" {!! $sort_by === 'rating,desc' ? 'selected' : '' !!}>{{ __('Legjobban értékeltek') }}</option>
                <option value="name,asc" {!! $sort_by === 'name,asc' ? 'selected' : '' !!}>{{ __('Betűrend, A-Z') }}</option>
                <option value="name,desc" {!! $sort_by === 'name,desc' ? 'selected' : '' !!}>{{ __('Betűrend, Z-A') }}</option>
            </select>
        </div>
        <input type="hidden" name="county_ids" id="" value="{{ $search_request->counties }}">
        <input type="hidden" name="city_ids" id="" value="{{ $search_request->cities }}">
        <input type="submit" value="{{ __('Alkalmazd') }}" name="submit" class="submit" />
    </form>

    <script>
        $(function () {
            // If category is selected on page load, set and select the subcategories as well.
            if(parseInt($('#category').val()) !== 0) {
                changeSubcategory($('#category').val());
            }
            $(document).on('change', '#category', function () {
                changeSubcategory($(this).val());
            });
        });

        changeSubcategory = function (parent_id) {
            var dropdown = $('#subcategory');
            if(parseInt(parent_id) === 0) {
                dropdown.empty();
                $('.subcategory-container').addClass('inactive');
            } else {
                $.getJSON('{{ url('category') }}/' + parent_id, function (data) {
                    dropdown.empty();
                    dropdown.append($('<option value="0">{{ __('Összes') }}</option>'));
                    $.each(data, function (key, entry) {
                        dropdown.append($('<option value="' + entry.id + '" ' + (parseInt(entry.id) === {{ $selected_subcategory }} ? 'selected' : '') + '>' + entry.name + '</option>'));
                    })
                    $('.subcategory-container').removeClass('inactive');
                });
            }
        };
    </script>

</div>