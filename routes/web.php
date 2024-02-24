<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserActionsController;
use App\Http\Controllers\VoteController;
use App\Models\Locality;
use Illuminate\Support\Facades\Route;


// Test controller
Route::any('test', [IndexController::class, 'home']);

/*
|--------------------------------------------------------------------------
| The home page
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('index', [
        'title' => __('Destination - Home'),
        'page_class' => 'home',
        'main_categories' => (new \App\Models\Category)->getCategoriesWithPlaceCount(0),
        'counties' => (new \App\Models\Locality)->getLocalitiesWithPlaceCount(0),
    ]);
})->name('home');

/*
|--------------------------------------------------------------------------
| Search related routes
|--------------------------------------------------------------------------
*/

Route::any('search/{slug?}/{field?}/{search_id?}', [SearchController::class, 'createSearchQuery'])->name('create_search_query');
Route::any('search', [SearchController::class, 'createSearchQuery'])->name('create_search_query');

/*
|--------------------------------------------------------------------------
| Localities related routes
|--------------------------------------------------------------------------
*/

Route::get('locality/{parent_id}', function ($parent_id) {
    return (new App\Models\Locality)->getLocalitiesWithPlaceCount($parent_id);
});
Route::post('locality/multiple', function () {
    if ((int) implode(',', request()->get('id_array')) === 0) {
        return (new App\Models\Locality)->getAllLocalities();
    } else {
        return Locality::getChildLocalitiesWithPlaceCountBasedOnIdArray(request()->get('id_array'));
    }
});

/*
|--------------------------------------------------------------------------
| Category related routes
|--------------------------------------------------------------------------
*/

// Get categories by parent ID
Route::get('category/{parent_id}', function ($parent_id) {
    return (new App\Models\Category)->getCategoriesWithPlaceCount($parent_id);
});

/*
|--------------------------------------------------------------------------
| Place related routes
|--------------------------------------------------------------------------
*/

// List all the places from a stored search
Route::get('list-places/{slug}/{id}', [PlaceController::class, 'list'])->name('list_places');

// Show place page
Route::get('show/{slug}/{place_id}', [PlaceController::class, 'show'])->name('show');

// Routes requiring authentication and admin or editor level
Route::middleware(['auth', 'isemailverified', 'isadminoreditor'])->group(function () {
    // View for creating a new place
    Route::get('create-place', function () {
        return view('place.create', ['title' => __('Create place'), 'page_class' => 'admin']);
    })->name('create_place');

    // Edit place
    Route::get('edit_place/{place_id}', [PlaceController::class, 'editPlace']);

    // Insert the place into the database
    Route::post('store', [PlaceController::class, 'store'])->name('store');

    // Update the place
    Route::post('update_place', [PlaceController::class, 'update'])->name('update_place');

    // Image upload page
    Route::get('upload_images/{place_id}', [PlaceController::class, 'uploadImage']);

    // Save an image
    Route::post('/save_image', [PlaceController::class, 'saveImage']);

    // Change the order of an image
    Route::get('/reorder_image/{direction}/{id}', [PlaceController::class, 'reorderImage']);

    // Delete image
    Route::get('/delete_image/{image_id}', [PlaceController::class, 'deleteImage']);
});

// Routes requiring authentication
Route::middleware(['auth', 'isemailverified'])->group(function () {
    // Add route to favorites
    Route::post('/add_route_to_favorites', [RouteController::class, 'addRouteToFavorites']);

    // Store vote
    Route::post('/store_vote', [VoteController::class, 'storeVote']);

    // Update route
    Route::get('/update_route/{action}/{route_id}', [RouteController::class, 'updateRoute']);

    // Delete route
    Route::get('/delete_saved_route/{route_id}', [RouteController::class, 'deleteSavedRoute']);

    // Show all routes from a suer
    Route::get('my-routes', [RouteController::class, 'routesList'])->name('my_routes');
});

/*
|--------------------------------------------------------------------------
| Route related routes
|--------------------------------------------------------------------------
*/

// Add a place ID (place_id) to route
Route::post('add-place-to-route', [RouteController::class, 'addOrRemovePlaceIdInRoute'])->name('add_place_to_route');

// Create route and add it to the database
Route::get('create-route', [RouteController::class, 'createRoute'])->name('my_route');

// Show a route
Route::get('show-route/{slug?}/{route_id?}', [RouteController::class, 'showRoute'])->name('show_route');

// Set a place as starting point
Route::get('/set-as-starting-point/{route_id}/{id}', [RouteController::class, 'setStartingPoint'])->name('set_as_starting_point');

// Set a place as ending point
Route::get('/set-as-ending-point/{route_id}/{id}', [RouteController::class, 'setEndingPoint'])->name('set_as_ending_point');

// Delete a destination (place) from the route
Route::get('/delete-destination/{route_id}/{id}', [RouteController::class, 'deleteDestination'])->name('delete_destination');

// Delete the whole route
Route::get('delete-route', function () {
    request()->session()->forget('my_route');
    return redirect()->to(request()->session()->get('search_route') ? request()->session()->get('search_route') : 'show-route');
})->name('delete_route');

// Add a place ID (place_id) to route
Route::post('route_number', [RouteController::class, 'countRoutes']);

/*
|--------------------------------------------------------------------------
| User related routes
|--------------------------------------------------------------------------
*/
Route::get('register', function () {
    return view('user.register', ['title' => __('Register'), 'page_class' => 'register']);
})->name('register');
Route::get('reset-password', function () {
    return view('user.reset-password', ['title' => __('Reset password'), 'page_class' => 'password-reset']);
})->name('reset_password');
Route::post('send-email-reset-password', [RegistrationController::class, 'sendPasswordResetEmail'])->name('send_email_reset_password');
Route::post('send-registration', [RegistrationController::class, 'registration'])->name('send_registration');
Route::get('confirm-registration/{user_id}/{unique_id}', [RegistrationController::class, 'confirmRegistration'])->name('confirm_registration');
Route::get('confirm-password-reset/{user_id}/{unique_id}', [RegistrationController::class, 'confirmPasswordReset'])->name('confirm_password_reset');
Route::get('login/{from_where?}', function () {
    return view('user.login', ['title' => __('Login'), 'page_class' => 'login']);
})->name('login');
Route::get('logout', [LoginController::class, 'logout'])->name('logout');
Route::post('authenticate', [LoginController::class, 'authenticate'])->name('authenticate');
Route::middleware(['auth', 'isemailverified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'show'])->name('dashboard');
    Route::get('my_places', [PlaceController::class, 'listUserPlaces'])->name('my_places');
    Route::post('update_password', [UserActionsController::class, 'update_password'])->name('update_password');
});
