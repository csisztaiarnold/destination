cl = function (l) {
    console.log(l)
};

/**
 * Populates a category or locality <select> field based on parent id.
 *
 * @param {string} select_field_selector The dropdown element's selector.
 * @param {int} parent_id The parent's ID.
 * @param {int} category_id The category ID.
 * @param {string} json_base_url The base of the request url.
 * @param {int} category_id Category ID.
 */
populateDropdown = function (select_field_selector, parent_id, category_id, json_base_url) {
    let dropdown = $(select_field_selector);
    const url = json_base_url + parent_id;
    let selected = '';

    if (parseInt(parent_id) !== 0) {
        dropdown.empty();
    }

    $.getJSON(url, function (data) {
        $.each(data, function (key, entry) {
            if (category_id !== 0) {
                if (category_id === entry.id) {
                    selected = 'selected';
                }
                console.log(category_id + ' ' + entry.id);
            }
            dropdown.append($('<option value="' + entry.id + '"' + selected + '>' + entry.name + ' (' + entry.number_of_places + ')</option>'));
            selected = '';
        })
    });
};

/**
 * Populates an <ul> element with <li> items from an array of IDs.
 * This function is used only once when the counties are selected on the home page.
 *
 * @param {string} ul_element_selector The dropdown element's selector.
 * @param {array} id_array The array of the parent IDs.
 * @param {string} json_base_url The base of the request url.
 * @param {string} all_string The "All" string.
 */
populateUlFromIdArray = function (ul_element_selector, id_array, json_base_url, all_string) {
    const url = json_base_url;
    let totalNumberOfPlaces = 0;

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    const data = {
        id_array: id_array,
    };
    $.ajax({
        url: url, type: 'POST', dataType: 'json', data: data,
        complete: function (data) {
            $.each(JSON.parse(data.responseText), function (key, entry) {
                totalNumberOfPlaces += entry.number_of_places;
                $(ul_element_selector).append($('<li data-id="' + entry.id + '" data-places="' + entry.number_of_places + '" class="item"><span>' + entry.name + '</span> (' + entry.number_of_places + ')</li>'));
            })
            $(ul_element_selector + ' .all').remove()
            $(ul_element_selector).append($('<li data-id="0" data-places="' + totalNumberOfPlaces + '" class="all active"><span>' + all_string + '</span> (' + totalNumberOfPlaces + ')</li>'));
            $(ul_element_selector + ' .all').prependTo(ul_element_selector);
        }
    });
};

/**
 * Collects all data-id values from child elements which have an `active` class,
 * returns an array with a single 0 value if 'All' was clicked.
 *
 * Example:
 *
 * <ul>
 *     <li class="active" data-id="1">This ID will be collected</li>
 *     <li class="active" data-id="2">This ID will be collected</li>
 *     <li data-id="3">This ID won't be collected</li>
 *     <li class="active" data-id="4">This ID will be collected</li>
 * </ul>
 *
 * @param {string} element_selector The parent selector.
 * @param {bool} from_back User clicked on "Back to counties" button?.
 * @return {array} An array of the IDs collected from the child elements.
 */
collectIdsFromActiveClasses = function (element_selector, from_back) {
    const listItem = element_selector;
    const parentElementSelector = '#' + listItem.parent().attr('id');
    const activeParentElementSelector = parentElementSelector + ' .active';
    let listItemIdArray = [];

    if (parseInt(listItem.data('id')) === 0) {
        // If 'All' was clicked, remove all active elements, except for 'All'.
        listItemIdArray = [0];
        $(activeParentElementSelector).removeClass('active');
        listItem.addClass('active');
    } else {
        // Remove class from 'All' if any other element is clicked, and set/reset active classes.
        $(parentElementSelector + ' .all').removeClass('active');
        if (from_back === false) {
            if (listItem.hasClass('active')) {
                listItem.removeClass('active');
            } else {
                listItem.addClass('active');
            }
        }
        // Collect active items only from the parent element.
        const activeItems = $(activeParentElementSelector);
        $.each(activeItems, function (index, item) {
            listItemIdArray.push(parseInt($(item).data('id')));
        });
    }
    return listItemIdArray;
}

/**
 * Sums all the numbers from data-places attribute which have an `active` class.
 * Clicking on all will return the sum of all elements with an `item` class.
 *
 * @param {string} element_selector The parent selector.
 * @return {int} Sum of elements.
 */
sumPlaces = function (element_selector) {
    const parentElementSelector = '#' + element_selector.parent().attr('id');
    const listItemIdArray = [];
    let items = $(parentElementSelector + ' .active');

    if ($(element_selector).hasClass('all')) {
        items = $(parentElementSelector + ' .item');
    }
    $.each(items, function (index, item) {
        listItemIdArray.push($(item).data('places'));
    });
    return parseInt(listItemIdArray.reduce((a, b) => a + b, 0));
}

/**
 * Simple filter for <li> elements.
 *
 * @param {string} element_id The element ID.
 */
searchCities = function (element_id) {
    let input, filter, ul, li, a, i, txtValue;
    input = document.getElementById('search');
    filter = input.value.toUpperCase();
    ul = document.getElementById(element_id);
    li = ul.getElementsByTagName('li');
    for (i = 0; i < li.length; i++) {
        a = li[i].getElementsByTagName("span")[0];
        txtValue = a.textContent || a.innerText;
        if (txtValue.toUpperCase().indexOf(filter) > -1) {
            li[i].style.display = "";
        } else {
            li[i].style.display = "none";
        }
    }
}

/**
 * Adds a location to route and adds an `active` class to the element.
 *
 * @param {int} place_id The place ID.
 * @param {string} url The request URL.
 * @param {string} active_text The text for the active button.
 * @param {string} inactive_text The text for the inactive button.
 * @param {int} route_length_limit The limit of the route length.
 */
addToRoute = function (place_id, url, active_text, inactive_text, route_length_limit) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    const data = {
        place_id: place_id,
    };
    $.ajax({
        url: url, type: 'POST', data: data,
        complete: function () {
            const addToRouteElement = $('.add-to-route-' + place_id);
            const selectedMarker = map.getMarkerById(place_id);
            if (addToRouteElement.hasClass('active')) {
                addToRouteElement.removeClass('active');
                addToRouteElement.text(active_text);
                if (selectedMarker) {
                    selectedMarker.setIcon(L.icon(
                        {
                            iconUrl: iconurl_not_in_route,
                            iconSize: iconsize,
                            iconAnchor: iconanchor,
                            popupAnchor: popupanchor
                        }
                    ));
                }
                removeItemOnce(routeArray, place_id);
            } else {
                addToRouteElement.addClass('active');
                addToRouteElement.text(inactive_text);
                if (selectedMarker) {
                    selectedMarker.setIcon(L.icon(
                        {
                            iconUrl: iconurl_in_route,
                            iconSize: iconsize,
                            iconAnchor: iconanchor,
                            popupAnchor: popupanchor
                        }
                    ));
                }
                routeArray.push(place_id);
            }

            // Set all inactive `add-to-route` elements disabled if the route length limit is reached.
            if (routeArray.length + 1 > route_length_limit) {
                $.each($('.add-to-route'), function () {
                    if ($(this).hasClass('active') === false) {
                        $(this).addClass('disabled');
                    }
                });
            } else {
                $('.add-to-route').removeClass('disabled');
            }

            // Change all route length values.
            $('.route-length').text(routeArray.length);
            if (routeArray.length > 1) {
                $('.my-route').removeClass('hidden');
                $('.route-add-notification').hide();
            } else {
                $('.route-add-notification').show().text(add_destination_to_route);
                if (routeArray.length === 1) {
                    $('.route-add-notification').text(one_more_destination_to_route);
                }
                $('.my-route').addClass('hidden');
            }
        }
    });
}

/**
 * Removing an item from an array.
 *
 * @param {array} arr The array.
 * @param {string} value The value.
 * @return {array} The returned array.
 */
removeItemOnce = function (arr, value) {
    const index = arr.indexOf(value);
    if (index > -1) {
        arr.splice(index, 1);
    }
    return arr;
}

/**
 * Hide/show main menu on scroll down/up.
 *
 */
mainMenuHide = function () {
    const header = $('header');
    const scrollUp = "visible";
    const scrollDown = "hidden";
    let lastScroll = 0;
    window.addEventListener("scroll", () => {
        var currentScroll = window.pageYOffset;
        if (currentScroll <= 0) {
            header.removeClass(scrollUp);
            return;
        }
        if (currentScroll > lastScroll && !header.hasClass(scrollDown)) {
            $('.route-add-notification').css('top', '5px');
            header.removeClass(scrollUp);
            header.addClass(scrollDown);
        } else if (currentScroll < lastScroll && header.hasClass(scrollDown)) {
            $('.route-add-notification').css('top', '90px');
            header.removeClass(scrollDown);
            header.addClass(scrollUp);
        }
        lastScroll = currentScroll;
    });
}

$(function () {
    mainMenuHide();
});

$(document).ready(function () {
    hamburgerMenu();
});

window.addEventListener("resize", () => {
    //resizeElementsOnWindowResize();
});

hamburgerMenu = function () {
    $('.menu-open-close').click(function () {
        if ($('.mainmenu').hasClass('active')) {
            $('.mainmenu').hide().removeClass('active');
            $('.menu-open-close .material-icons').text('menu');
        } else {
            $('.mainmenu').show().addClass('active');
            $('.menu-open-close .material-icons').text('close');
        }
    });
}

/**
 * Function to get map marker by location ID
 */
L.Map.include({
    getMarkerById: function (id) {
        let marker = null;
        this.eachLayer(function (layer) {
            if (layer instanceof L.Marker) {
                if (layer.options.markerId === id) {
                    marker = layer;
                }
            }
        });
        return marker;
    }
});



