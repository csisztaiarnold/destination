@extends('base')

@section('main')

    <div class="home-hero-wrapper">

        <div class="home-hero">

            <img class="bg-layer background-airplane_1" src="{{ asset('img/headeranim/airplane2.svg') }}"/>
            <img class="bg-layer background-airplane_1" src="{{ asset('img/headeranim/airplane2.svg') }}"/>
            <img class="bg-layer background-airplane_2" src="{{ asset('img/headeranim/airplane2.svg') }}"/>
            <img class="bg-layer background-clouds" src="{{ asset('img/headeranim/clouds.svg') }}"/>
            <img class="bg-layer background-layer background-layer_1" src="{{ asset('img/headeranim/skyline_2.svg') }}"/>
            <img class="bg-layer background-layer background-layer_2" src="{{ asset('img/headeranim/skyline_3.svg') }}"/>
            <img class="bg-layer background-layer background-layer_3" src="{{ asset('img/headeranim/skyline_1.svg') }}"/>
            <img class="bg-layer background-layer background-layer_4" src="{{ asset('img/headeranim/skyline_4.svg') }}"/>
            <img class="bg-layer background-layer background-layer_5" src="{{ asset('img/headeranim/skyline_5.svg') }}"/>

            <div id="counties-filter-container" class="noselect">
                <h1>{{ __('Hova utaznál?') }}</h1>
                <div class="list-wrapper">
                @if(count($counties) > 0)
                    <ul id="locality-menu" class="filter-menu">
                        <li data-id="0" class="all">{{ __('Az összes') }}
                            <span class="sum-of-all-places"><!-- Value added from JS --></span>
                        </li>
                        @foreach($counties as $county)
                            <li data-id="{{ $county->id }}" data-places="{{ $county->number_of_places }}" class="item">
                                {{ $county->name }} ({{ $county->number_of_places }})
                            </li>
                        @endforeach
                    </ul>
                @endif
                </div>
                <div id="sum-of-places-in-selected-localities"></div>
            </div>

            <div id="cities-filter-container" class="noselect">
                <h1>{{ __('Mely helyeket látogatnád meg?') }}</h1>
                <input type="text" id="search" onkeyup="searchCities('city-menu')" placeholder="{{ __('Hely keresése...') }}"/>
                <div class="list-wrapper">
                    <ul id="city-menu" class="filter-menu">
                    </ul>
                </div>
                <div id="start-exploring">{{ __('Kezd el a felfedezést!') }}
                    <span><img src="{{ asset('img/next.svg') }}" alt="Next"/></span>
                </div>
                <br />
                <div id="back-to-counties">
                    <span><img src="{{ asset('img/back.svg') }}" alt="Back"/></span> {{ __('Vissza a megyékre') }}
                </div>
            </div>
        </div>

        <form name="home-search" id="home-search" method="post" action="{{ route('create_search_query') }}">
            @csrf
            <input type="hidden" name="county_ids" id="county_ids" value="">
            <input type="hidden" name="city_ids" id="city_ids" value="">
        </form>

    </div>

    <script>
        $(function () {
            $('#locality-menu .all').addClass('active');
            var sumOfPlaces = sumPlaces($('#locality-menu .all'));
            var buttonElement = $('#sum-of-places-in-selected-localities');
            var selectedIds = [0];
            var selectedElement;

            $('#locality-menu .all .sum-of-all-places').html('(' + sumOfPlaces + ')'); // Count for "All"
            buttonElement.html(sumOfPlaces + ' {{ __(' felfedezésre váró hely!') }}'); // Initial button text

            $(document).on('click', '#locality-menu li, #city-menu li, #back-to-counties', function () {
                var fromBack = false;

                if ($(this).attr('id') === 'back-to-counties') {
                    selectedElement = $('#locality-menu li.active');
                    $('#city-menu li').removeClass('active');
                    collectIdsFromActiveClasses(selectedElement, true);
                    fromBack = true;
                    selectedIds = [0];
                } else {
                    selectedElement = $(this);
                }
                selectedIds = collectIdsFromActiveClasses(selectedElement, fromBack);
                sumOfPlaces = sumPlaces(selectedElement);
                if (sumOfPlaces > 0) {
                    buttonElement.removeClass('inactive');
                    buttonElement.html(sumOfPlaces + ' {{ __(' felfedezésre váró hely!') }}');
                } else {
                    buttonElement.addClass('inactive');
                    buttonElement.html('{{ __('Válassz ki legalább egy megyét!') }}');
                }
                if (selectedElement.parent().attr('id') === 'locality-menu') {
                    $('#county_ids').val(selectedIds);
                } else {
                    $('#city_ids').val(selectedIds);
                }
            });

            $(document).on('click', '#sum-of-places-in-selected-localities', function () {
                if (!$(this).hasClass('inactive')) {
                    $('#counties-filter-container').removeClass('visible').addClass('hidden');
                    $('#cities-filter-container').removeClass('hidden').addClass('visible');
                    $('#city-menu').empty();
                    populateUlFromIdArray('#city-menu', selectedIds, '{{ url('locality') }}/multiple', '{{ __('All') }}');
                    $('#city-menu .all').addClass('active');
                }
            });

            $(document).on('click', '#back-to-counties', function () {
                $('#search').val('');
                $('#counties-filter-container').removeClass('hidden').addClass('visible');
                $('#cities-filter-container').removeClass('visible').addClass('hidden');
            });

            $(document).on('click', '#start-exploring', function () {
                $('#home-search').submit();
            });
        });
    </script>

@endsection
