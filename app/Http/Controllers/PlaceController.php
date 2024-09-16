<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Image;
use App\Models\Locality;
use App\Models\Place;
use App\Models\Route;
use App\Models\Search;
use App\Scopes\StatusScope;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\ImageManager;

class PlaceController extends Controller
{
    /**
     * @var Category
     */
    protected $category;

    /**
     * @var Image
     */
    protected $image;

    /**
     * @var int
     */
    private $image_upload_limit = 5;

    /**
     * @var ImageManager
     */
    private $interventionImage;

    /**
     * @var Locality
     */
    protected $locality;

    /**
     * @var Place
     */
    protected $place;

    /**
     * @var Route
     */
    protected $route;

    /**
     * @var Search
     */
    protected $search;

    /**
     * PlaceController constructor.
     *
     * @param Category $category
     * @param Image $image
     * @param ImageManager $intervention_image
     * @param Locality $locality
     * @param Place $place
     * @param Route $route
     * @param Search $search
     */
    public function __construct(
        Category $category,
        Image $image,
        ImageManager $intervention_image,
        Locality $locality,
        Place $place,
        Route $route,
        Search $search
    ) {
        $this->category = $category;
        $this->image = $image;
        $this->interventionImage = $intervention_image;
        $this->locality = $locality;
        $this->place = $place;
        $this->route = $route;
        $this->search = $search;
    }

    /**
     * List submissions based on search query.
     *
     * @param string $slug The search query slug
     * @param int $search_id The search ID
     * @return Application|Factory|View
     */
    public function list(string $slug, int $search_id)
    {
        $search_request = $this->search->getSearchQuery($search_id);
        $category_id = $search_request->category;
        $subcategory_id = $search_request->subcategory;
        $city_ids = $search_request->cities;
        $county_ids = $search_request->counties;
        $order_items = explode(',', $search_request->sort_by);
        $order = !empty($order_items[0]) ? $order_items[0] : 'created_at';
        $order_direction = !empty($order_items[1]) ? $order_items[1] : 'desc';
        $paginate = 20;

        $places = $this->place->getPlacesBySearchQuery(
            $subcategory_id,
            $county_ids,
            $city_ids,
            $order,
            $order_direction,
            $paginate
        );
        $markers = $this->place->getPlacesBySearchQuery(
            $subcategory_id,
            $county_ids,
            $city_ids,
            $order,
            $order_direction,
            1
        );
        $search_string = $this->place->createTitleArrayBySearchId($search_id);

        $title_separator = (!empty($search_string['county_string']) && !empty($search_string['city_string'])) ? ' - ' : '';
        $title = $search_string['county_string'] . $title_separator . $search_string['city_string'];
        $page_title_separator = (!empty($search_string['county_string']) && !empty($search_string['city_string'])) ? '<br />' : '';
        $page_title = $search_string['county_string'] . $page_title_separator . $search_string['city_string'];

        return view('place.search', [
            'search_request' => $search_request,
            'places' => $places,
            'markers' => $markers,
            'title' => empty($title) ? __('Az összes megye és város') : $title,
            'page_title' => empty($page_title) ? __('Az összes megye és város') : $page_title,
            'page_class' => 'search',
            'categories' => $this->category->getCategoriesWithPlaceCount(0),
            'selected_category' => (int)$category_id,
            'selected_subcategory' => (int)$subcategory_id,
            'sort_by' => $search_request->sort_by,
        ]);
    }

    /**
     * List submissions from a logged-in user.
     *
     * @return Application|Factory|View
     */
    public function listUserPlaces()
    {
        if (Auth::user()->role === 'admin') {
            $places = $this->place->withoutGlobalScope(StatusScope::class)->get();
        } else {
            $places = $this->place->withoutGlobalScope(StatusScope::class)->where('user_id', auth()->id())->get();
        }
        return view('user.my-places', [
            'places' => $places,
            'page_class' => 'admin',
        ]);
    }

    /**
     * Shows the place.
     *
     * @param string $slug The place's slug
     * @param int $place_id The place's ID
     * @return Application|Factory|View
     */
    public function show(string $slug, int $place_id)
    {
        $place = $this->place->withoutGlobalScope(StatusScope::class)->selectRaw(
            '*, (SELECT `name` FROM `localities` WHERE id = places.city_id) as city_name, (SELECT `name` FROM `localities` WHERE id = places.county_id) as county_name'
        )->where('id', $place_id)->first();
        if ($place->status === 'active' || ($place->user_id === auth()->id() && $place->status !== 'active') || (isset(Auth::user()->role) && Auth::user()->role === 'admin')) {
            return view('place.show', [
                'page_class' => 'place-page',
                'title' => $place->name,
                'single_place' => $place,
                'nearby_places' => $this->place->nearbyPlaces($place->id, 30, $place->latitude, $place->longitude),
                'routes' => $this->route->getPublicRoutesWithLocation($place_id),
                'my_route_array' => $this->route->getRouteArray(),
                'route_length_limit' => $this->route->getRouteLenghtLimit(),
                'uploaded_images' => $uploaded_images = $this->image->getImagesByPlaceId($place_id),
            ]);
        } else {
            return view('error', [
                'page_class' => 'error-page',
                'title' => __('Sorry, this place isn\'t active yet.'),
                'message' => __('Sorry, this place isn\'t active yet.'),
            ]);
        }
    }

    /**
     * Store the place.
     *
     * @return void
     * @throws Exception
     */
    public function store()
    {
        $new_order = $this->place->max('row_order') + 1;

        request()->validate([
            'name' => 'required',
            'category_id' => 'required',
            'subcategory_id' => 'required',
            'city_id' => 'required',
            'county_id' => 'required',
            'latitude' => ['required', 'regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/'],
            'longitude' => ['required', 'regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/'],
        ]);

        $data = [
            'unique_id' => generateRandomString(32),
            'user_id' => auth()->id(),
            'city_id' => request()->input('city_id'),
            'county_id' => request()->input('county_id'),
            'name' => request()->input('name'),
            'description' => request()->input('description'),
            'latitude' => number_format(request()->input('latitude'), 8, '.', ''),
            'longitude' => number_format(request()->input('longitude'), 8, '.', ''),
            'tags' => empty(request()->input('tags')) ? '' : request()->input('tags'),
            'address' => request()->input('address'),
            'facebook_page' => request()->input('facebook_page'),
            'website' => request()->input('website'),
            'accessible_with_car' => empty(request()->input('accessible_with_car')) ? 0 : 1,
            'disabled_accessible' => empty(request()->input('disabled_accessible')) ? 0 : 1,
            'kid_friendly' => empty(request()->input('kid_friendly')) ? 0 : 1,
            'row_order' => $new_order,
        ];

        request()->merge($data);
        $input = request()->all();
        $place = $this->place->create($input);
        DB::table('category_places')->insert([
            ['place_id' => $place->id, 'category_id' => request()->input('category_id')],
            ['place_id' => $place->id, 'category_id' => request()->input('subcategory_id')],
        ]);

        session()->flash('message', __('The post was successfully created!'));

        // Send email to administrator
        dd('place inserted');
    }

    /**
     * Edit place.
     *
     * @param int $place_id The place ID
     * @return Application|Factory|View|void
     */
    public function editPlace(int $place_id)
    {
        $place = $this->place->placeOwnership($place_id, auth()->id());
        if (isset($place->id)) {
            $categories = $place->withoutGlobalScope(StatusScope::class)->find($place_id)->categories;
            $category_id = $categories[0]->id;
            $subcategory_id = $categories[1]->id;
            if ($categories[1]->parent_id === 0) {
                $category_id = $categories[1]->id;
                $subcategory_id = $categories[0]->id;
            }
            return view('place.edit', [
                'place' => $place,
                'category_id' => $category_id,
                'subcategory_id' => $subcategory_id,
                'page_class' => 'admin',
            ]);
        } else {
            dd('Unauthorized'); // TODO: refactor this!
        }
    }

    /**
     * Update the place.
     *
     * @return RedirectResponse|void
     */
    public function update()
    {
        $place = $this->place->placeOwnership(request()->input('place_id'), auth()->id());

        if (isset($place->id)) {
            request()->validate([
                'name' => 'required',
                'category_id' => 'required',
                'subcategory_id' => 'required',
                'city_id' => 'required',
                'county_id' => 'required',
                'latitude' => ['required', 'regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/'],
                'longitude' => ['required', 'regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/'],
            ]);

            $this->place->withoutGlobalScope(StatusScope::class)
                ->find($place->id)
                ->categories()
                ->sync([request()->input('category_id'), request()->input('subcategory_id')]);

            $data = [
                'city_id' => request()->input('city_id'),
                'county_id' => request()->input('county_id'),
                'name' => request()->input('name'),
                'description' => request()->input('description'),
                'latitude' => number_format(request()->input('latitude'), 8, '.', ''),
                'longitude' => number_format(request()->input('longitude'), 8, '.', ''),
                'tags' => empty(request()->input('tags')) ? '' : request()->input('tags'),
                'address' => request()->input('address'),
                'facebook_page' => request()->input('facebook_page'),
                'website' => request()->input('website'),
                'accessible_with_car' => empty(request()->input('accessible_with_car')) ? 0 : 1,
                'disabled_accessible' => empty(request()->input('disabled_accessible')) ? 0 : 1,
                'kid_friendly' => empty(request()->input('kid_friendly')) ? 0 : 1,
            ];

            $data_status = [];
            if (Auth::user()->role === 'admin') {
                $data_status = [
                    'status' => request()->input('status') !== null ? 'active' : 'inactive',
                ];
            }

            $this->place->withoutGlobalScope(StatusScope::class)->where('id', $place->id)->update(
                array_merge($data, $data_status)
            );
            session()->flash('message', __('The post was successfully updated!'));
            return redirect()->back();
        } else {
            dd('Unauthorized'); // TODO: refactor this!
        }
    }

    /**
     * Upload images.
     *
     * @return Application|Factory|View|void
     */
    public function uploadImage($place_id)
    {
        $place = $this->place->placeOwnership($place_id, auth()->id());

        if (isset($place->id)) {
            $uploaded_images = $this->image->where('place_id', $place_id)->orderBy('row_order', 'asc')->get();
            return view('place.upload-image')->with([
                'place' => $place,
                'uploaded_images' => $uploaded_images,
                'image_upload_limit' => $this->place->getImageUploadLimit(auth()->user(), $this->image_upload_limit),
                'number_of_uploaded_images' => count($uploaded_images),
                'max_order' => $this->image->where('place_id', $place_id)->max('row_order'),
                'page_class' => 'admin',
            ]);
        } else {
            dd('Unauthorized'); // TODO: refactor this!
        }
    }

    /**
     * Save an image.
     *
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function saveImage(): RedirectResponse
    {
        $place = $this->place->placeOwnership(request()->input('place_id'), auth()->id());
        if (isset($place->id)) {
            $number_of_uploaded_images = count($this->image->where('place_id', $place->id)->get());
            if ($number_of_uploaded_images < $this->place->getImageUploadLimit(
                    auth()->user(),
                    $this->image_upload_limit
                )) {
                $new_order = $this->image->where('place_id', $place->id)->max('row_order') + 1;

                $this->validate(request(), [
                    'image' => 'required|image|mimes:jpg,jpeg,gif,png|max:10000',
                ]);

                request()->merge([
                    'filename' => $filename = Str::slug($place->name),
                    'extension' => $extension = request()->file('image')
                        ->getClientOriginalExtension(),
                    'unique_id' => generateRandomString(32),
                    'row_order' => $new_order,
                    'place_id' => $place->id,
                ]);

                $request_all = request()->all();

                $id = $this->image->create($request_all)->id;

                request()->file('image')->move(
                    base_path() . '/public/item_images/' . $place->id,
                    $filename . '_' . $id . '.' . $extension
                );

                // Create thumbnails
                $thumbnail_filename = base_path() . '/public/item_images/' . $place->id . '/' . $filename . '_' . $id;
                $thumbnail = $this->interventionImage->make($thumbnail_filename . '.' . $extension)
                    ->resize('150', '150', static function ($c) {
                        $c->aspectRatio();
                    });
                $thumbnail->save($thumbnail_filename . '_thumb.' . $extension);

                $thumbnail_mid = $this->interventionImage->make($thumbnail_filename . '.' . $extension)
                    ->resize('450', '450', static function ($c) {
                        $c->aspectRatio();
                    });
                $thumbnail_mid->save($thumbnail_filename . '_mthumb.' . $extension);
            }
            return redirect()->back();
        } else {
            dd('Unauthorized'); // TODO: refactor this!
        }
    }

    /**
     * Delete an image and update the image order.
     *
     * @param int $image_id The image ID
     *
     * @return RedirectResponse
     */
    public function deleteImage(int $image_id): RedirectResponse
    {
        $image = $this->image->where('id', $image_id)->first();
        $place = $this->place->placeOwnership($image->place_id, auth()->id());
        if (isset($place->id)) {
            // Delete the image from the database
            $this->image->where('id', $image_id)->delete();

            // Reduce order by one where the order of an image is higher than the current order
            $this->image->reduceOrderWhereOrderIsGreaterThanCurrentOrder($image->row_order, $place->id);

            // Delete the image files
            $image_filename = base_path() . '/public/item_images/' . $image->place_id . '/' . $image->filename . '_' . $image->id;
            unlink($image_filename . '.' . $image->extension);
            unlink($image_filename . '_thumb.' . $image->extension);
            unlink($image_filename . '_mthumb.' . $image->extension);

            return redirect()->back();
        } else {
            dd('Unauthorized'); // TODO: refactor this!
        }
    }


    /**
     * Update the order of images.
     *
     * @param string $direction The direction of the reorder
     * @param int $image_id The image ID
     *
     * @return RedirectResponse
     */
    public function reorderImage(string $direction, int $image_id): RedirectResponse
    {
        $current_image = $this->image->where('id', $image_id)->first();
        $place = $this->place->placeOwnership($current_image->place_id, auth()->id());
        if (isset($place->id)) {
            // The current image and current order
            $current_order = $current_image->row_order;

            // The highest order of an image for the submission
            $max_order = $this->image->where('place_id', $place->id)->max('row_order');

            // Reorder based on direction
            // TODO: Move this to a separate function
            if ($direction === 'down') {
                $new_order = $current_order + 1;
                if ($current_order === $max_order) {
                    $new_order = $max_order;
                }
            } else {
                $new_order = $current_order - 1;
                if ($current_order === 1) {
                    $new_order = 1;
                }
            }

            // Get the image to exchange order with
            $image_to_exchange = $this->image->where('row_order', $new_order)
                ->where('place_id', $place->id)
                ->first();

            // Reorder the images
            if ($image_to_exchange->id !== $current_image->id) {
                // Update the order of the current image
                $this->image->updateImageOrder($current_image->id, $place->id, $new_order);
                // Update the order of the exchanged image
                $this->image->updateImageOrder($image_to_exchange->id, $place->id, $current_order);
            }

            return redirect()->back();
        } else {
            dd('Unauthorized'); // TODO: refactor this!
        }
    }
}
