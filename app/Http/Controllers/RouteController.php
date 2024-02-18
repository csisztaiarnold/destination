<?php

namespace App\Http\Controllers;

use App\Models\Place;
use App\Models\Route;
use App\Models\UserRoute;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class RouteController extends Controller
{
    /**
     * @var Guzzle
     */
    private $guzzle;

    /**
     * @var Place
     */
    protected $place;

    /**
     * @var Route
     */
    protected $route;

    /**
     * @var UserRoute
     */
    protected $user_route;

    /**
     * RouteController constructor.
     *
     * @param Guzzle    $guzzle
     * @param Place     $place
     * @param Route     $route
     * @param UserRoute $user_route
     */
    public function __construct(Guzzle $guzzle, Place $place, Route $route, UserRoute $user_route)
    {
        $this->guzzle = $guzzle;
        $this->place = $place;
        $this->route = $route;
        $this->user_route = $user_route;
    }

    /**
     * Adds or removes a place id in the route session.
     *
     * - Creates a route session if it doesn't exits
     * - Adds a place ID if it isn't present in the route
     * - Removes a place ID if it's present in the route
     * - A new place is always added to the penultimate position of the array
     *
     * @return void
     */
    public function addOrRemovePlaceIdInRoute(): void
    {
        if (request()->session()->get('my_route') !== null) {
            $path = request()->session()->get('my_route');
        } else {
            $path = [];
        }
        $place_id = (int)request()->input('place_id');

        // Delete the item if it's already in the array
        if (in_array($place_id, $path, true)) {
            unset($path[array_search($place_id, $path, true)]);
        } else {
            // Set limit of locations in the route
            $route_count = request()->session()->get('my_route') !== null ? count(
                request()->session()->get('my_route')
            ) : 0;
            if ($route_count < $this->route->getRouteLenghtLimit()) {
                // Put the location to the penultimate position in the array to avoid removing current endpoint
                $elements = count($path);
                if ($elements > 1) {
                    $path = array_values(
                        array_slice($path, 0, $elements - 1, true) +
                        ['' => $place_id] +
                        array_slice($path, $elements - 1, $elements - 1, true)
                    );
                } else {
                    array_push($path, $place_id);
                }
            }
        }

        request()->session()->put('my_route', $path);
    }

    /**
     * Stores a route and queries the OSRM router if it doesn't exists in the database yet.
     *
     * @throws GuzzleException
     */
    public function createRoute()
    {
        $my_route = request()->session()->get('my_route');

        if (!is_array($my_route)) {
            request()->session()->flash('message', __('Your route is empty.'));
            return redirect('show-route');
        }

        // A route needs at least two places.
        if (count($my_route) < 2) {
            request()->session()->flash('message', __('Please add at least two destinations to your route.'));
            return redirect('show-route');
        }

        // Check if the route is already stored in the database,
        // there's no need to send a request the router.
        $route = $this->route->firstOrCreate([
            'route' => implode(',', $my_route),
        ]);

        if ($route->wasRecentlyCreated === true) {
            $places = $this->place->getLatLangFromPlaces($my_route);

            // Create an array that consists of the place IDs only for waypoint comparison.
            $wp_place_id_array = [];
            foreach ($places as $place) {
                // There should be lat and lng data for all the posts, but just to make it sure.
                if (isset($place->longitude, $place->latitude)) {
                    $wp_place_id_array[] = $place->id;
                }
            }

            // Get the best route waypoints from OSRM the router.
            $waypoints = $this->route->queryOsrmForWaypoints($places);

            // They waypoints in the response are sorted in the requested order,
            // so sort them by the waypoint index to get the shortest route.
            $place_id_index = 0;
            foreach ($waypoints->waypoints as $waypoint) {
                $waypoint_index_array[$waypoint->waypoint_index] = [
                    $waypoint->location[1],
                    $waypoint->location[0],
                    $wp_place_id_array[$place_id_index],
                ];
                $waypoint_route_id_array[$waypoint->waypoint_index] = $wp_place_id_array[$place_id_index];
                $place_id_index++;
            }
            ksort($waypoint_route_id_array);

            // Store the route and the optimal route to the database,
            // so we could reuse the data without sending another query to OSRM.
            $best_route = implode(',', $waypoint_route_id_array);

            $this->route->where('id', $route->id)
                ->update([
                    'best_route' => $best_route,
                ]);
        }
        $places = $this->place->getPlacesByBestRouteIdArray(
            'name',
            explode(',', $this->route->where('id', $route->id)->first()->best_route)
        );
        $slug = '';
        $place_count = 0;
        $name = '';
        foreach ($places as $place) {
            if ($place_count === 0) {
                $name .= $place->name . ' - ';
            }
            if ($place_count === count($places) - 1) {
                $name .= $place->name;
            }
            $slug .= $place->name . ' ';
            $place_count++;
        }
        $route_slug = Str::slug($slug);
        $this->route->where('id', $route->id)
            ->update([
                'slug' => $route_slug,
                'name' => $name,
            ]);
        return redirect('show-route/' . $route_slug . '/' . $route->id);
    }

    /**
     * Shows the saved route.
     *
     * @param string $slug     The route slug
     * @param int    $route_id The route ID
     * @return Application|Factory|View
     */
    public function showRoute(string $slug = '', int $route_id = 0)
    {
        $route = $this->route->where('id', $route_id)->first();
        $places = [];
        $error = '';
        if (isset($route->id)) {
            $places = $this->place->getPlacesByBestRouteIdArray('*', explode(',', $route->best_route));
            if ($route->public !== 1) {
                $error = __('This route is private.');
            }
        } else {
            $error = __('Your route is empty.');
            if ($route_id !== 0) {
                $error = __('This route has been deleted or it never existed.');
            }
        }

        request()->session()->put('last_route_page', url()->current());

        return view('route.show', [
            'places' => $places,
            'error' => $error,
            'route' => $route,
            'lat_lng_js_string' => '',
            'popup_text_js_string' => '',
            'page_class' => 'route-page',
            'title' => isset($route->name) ? __('Your route: :route_name', ['route_name' => $route->name]) : '',
        ]);
    }

    /**
     * Set a starting point of a route.
     *
     * @param int $route_id The route ID
     * @param int $place_id The place ID
     *
     * @return RedirectResponse
     */
    public function setStartingPoint(int $route_id, int $place_id)
    {
        $route = $this->route->where('id', $route_id)->first();

        // Delete the location ID from the session array.
        $route_array = explode(',', $route->best_route);
        if (($key = array_search((int)$place_id, $route_array, true)) !== false) {
            unset($route_array[$key]);
        }

        // Put the location ID at the start of the session array.
        $route_array = Arr::prepend($route_array, $place_id);

        request()->session()->put('my_route', array_values($route_array));

        return redirect()->to(route('my_route'));
    }

    /**
     * Set an ending point of a route.
     *
     * @param int $route_id The route ID
     * @param int $place_id The place ID
     *
     * @return RedirectResponse
     */
    public function setEndingPoint(int $route_id, int $place_id)
    {
        $route = $this->route->where('id', $route_id)->first();

        // Delete the location ID from the route.
        $route_array = explode(',', $route->best_route);
        if (($key = array_search((int)$place_id, $route_array)) !== false) {
            unset($route_array[$key]);
        }

        // Put the location ID at the end of the session array
        $route_array[] = $place_id;

        request()->session()->put('my_route', $route_array);

        return redirect(route('my_route'));
    }

    /**
     * Delete a location from the route.
     *
     * @param int $route_id The route ID
     * @param int $place_id The place ID
     *
     * @return Application|Factory|View|RedirectResponse
     */
    public function deleteDestination(int $route_id, int $place_id)
    {
        $route = $this->route->where('id', $route_id)->first();

        // Delete the location ID from the route.
        $route_array = explode(',', $route->best_route);

        // It shouldn't happen, but don't allow less than two destinations in the route
        if (count($route_array ?? []) > 2) {
            if (($key = array_search((int)$place_id, $route_array)) !== false) {
                unset($route_array[$key]);
            }
            request()->session()->put('my_route', $route_array);
            return redirect(route('my_route'));
        } else {
            return view('error')->with([
                'title' => __('Error'),
                'message' => __('You have tried to delete a destination that doesn\'t exists.'),
                'help' => __('Don\'t worry, this isn\'t a critical problem, just continue with browsing the site.'),
            ]);
        }
    }

    /**
     * Adds route to favorites. TODO: finish the function!
     *
     * @return void
     */
    public function addRouteToFavorites()
    {
        $this->user_route->updateOrCreate([
            'user_id' => auth()->id(),
            'route_id' => request()->input('route_id'),
            'name' => empty(request()->input('route_name')) ? __('Névtelen útiterv (') . date(
                    'Y-m-d H:i:s'
                ) . ')' : request()->input('route_name'),
            'public' => request()->input('route_public') === "false" ? 0 : 1,
        ]);
    }

    /**
     * Save a route
     */
    public function saveRoute(): RedirectResponse
    {
        $route = $this->route->getSingleRouteByPath(
            implode(',', request()->session()->get('mypath')),
            request()->session()->get('starting_point_location')
        );

        if ($this->user_route->routeAlreadySaved($route->id ?? null, auth()->id()) === true) {
            return redirect()->back();
        } else {
            $this->user_route->insert([
                'user_id' => auth()->id(),
                'route_id' => $route->id,
                'name' => request()->input('route_name') ? __('Névtelen útiterv (') . date(
                        'Y-m-d H:i:s'
                    ) . ')' : request()->input('route_name'),
                'public' => request()->input('public'),
            ]);
            request()->session()->flash('message', __('Route was successfully saved!'));
            return redirect()->to('my_routes');
        }
    }

    /**
     * Count routes
     *
     * @return int
     */
    public function countRoutes(): int
    {
        return count(request()->session()->get('my_route') === null ? [] : request()->session()->get('my_route'));
    }

    /**
     * Show a list of stored routes
     *
     * @return Factory|\Illuminate\View\View
     */
    public function routesList()
    {
        return view('user.list_routes')->with([
            'routes' => $this->user_route
                ->select(
                    '*',
                    \DB::raw(
                        "user_routes.id as route_id, user_routes.name as route_name, user_routes.public as public, user_routes.user_id as user_id"
                    )
                )
                ->where('user_routes.user_id', auth()->id())
                ->leftJoin('routes', 'routes.id', '=', 'route_id')
                ->get(),
            'title' => __('My routes'),
            'page_class' => 'route-list',
        ]);
    }

    /**
     * Set a route to public or private
     *
     * @param $action
     * @param $route_id
     *
     * @return RedirectResponse
     */
    public function updateRoute($action, $route_id) {
        $public = NULL;
        if ($action === 'set_public') {
            $public = 1;
        }
        $this->user_route->where('route_id', $route_id)
            ->where('user_id', auth()->id())
            ->update([
                'public' => $public,
            ]);
        return redirect()->back();
    }

    /**
     * Delete a stored route
     *
     * @param $route_id
     *
     * @return RedirectResponse
     */
    public function deleteSavedRoute($route_id) {
        $this->user_route->where('route_id', $route_id)
            ->where('user_id', auth()->id())
            ->delete();
        return redirect()->back();
    }
}
