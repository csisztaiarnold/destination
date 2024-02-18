<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Locality;
use App\Models\Search;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    /**
     * @var Category
     */
    protected $category;

    /**
     * @var Locality
     */
    protected $locality;

    /**
     * @var Search
     */
    protected $search;

    /**
     * SearchController constructor.
     *
     * @param Category $category
     * @param Locality $locality
     * @param Search $search
     */
    public function __construct(Category $category, Locality $locality, Search $search)
    {
        $this->category = $category;
        $this->locality = $locality;
        $this->search = $search;
    }

    /**
     * Stores the search query in a database and creates a unique URL for it.
     *
     * @param string $slug The search slug
     * @param string $field The database field
     * @param int $id The search ID
     * @return RedirectResponse
     */
    public function createSearchQuery(string $slug = '', string $field = '', int $id = 0): RedirectResponse
    {
        $city_ids = (string)request()->input('city_ids') === "0" ? "" : (string)request()->input('city_ids');
        $county_ids = (string)request()->input('county_ids') === "0" ? "" : (string)request()->input('county_ids');
        $category = (string)request()->input('category') === "0" ? "" : (string)request()->input('category');
        $subcategory = (string)request()->input('subcategory') === "0" ? "" : (string)request()->input('subcategory');
        $sort_by = (string)request()->input('sort_by') === "0" ? "" : (string)request()->input('sort_by');

        if ($field !== '') {
            switch ($field) {
                case 'city':
                    $city_ids = $id;
                    break;
                case 'county':
                    $county_ids = $id;
                    break;
                case 'category':
                    $category_id = $id;
                    break;
                case 'subcategory':
                    $subcategory_id = $id;
                    break;
            }
        }

        $city_slug = $this->returnLocalitySlugFromIdCsv($city_ids);
        $county_slug = $this->returnLocalitySlugFromIdCsv($county_ids);
        $category_slug = $this->returnCategorySlugFromIdCsv($category);
        $subcategory_slug = $this->returnCategorySlugFromIdCsv($subcategory);
        $sort_by_items = explode(',', $sort_by);

        $order = $sort_by_items[0] ?? '';
        $order_direction = $sort_by_items[1] ?? '';

        $slug = Str::slug(
            implode(' ', [
                $county_slug,
                $city_slug,
                $category_slug,
                $subcategory_slug,
                $order,
                $order_direction,
                ' ',
            ])
        );

        $sort_by = (in_array($order, ['name', 'created_at', 'rating']) ? $order : 'created_at') . ',' . (in_array($order_direction, ['asc', 'desc']) ? $order_direction : 'desc');

        $data = [
            'cities' => $city_ids,
            'counties' => $county_ids,
            'category' => $category,
            'subcategory' => $subcategory,
            'sort_by' => $sort_by,
        ];

        // If this particular search query doesn't exist, insert it into the database
        $search = $this->search->firstOrCreate([
            'search_requests' => json_encode($data),
        ]);

        $route = route('list_places', ['slug' => $slug, 'id' => $search->id]);
        request()->session()->put('search_route', $route);
        return redirect()->to($route);
    }

    /**
     * Creates a slug for the URL from the locality ID CSV.
     *
     * @param string $id_csv The ID CSV
     * @return string
     */
    private function returnLocalitySlugFromIdCsv(string $id_csv): string
    {
        if ($id_csv === "") {
            $slug = __('all');
        } else {
            // Get localities
            $localities = $this->locality->getLocalitiesBasedOnIdArray(explode(',', $id_csv));
            // Create a locality slug
            $sting = '';
            foreach ($localities as $locality) {
                $sting .= $locality->name . '-';
            }
            $slug = Str::slug($sting, '-');
        }
        return $slug;
    }

    /**
     * Creates a slug for the URL from the category ID CSV.
     *
     * @param string $id_csv The ID CSV
     * @return string
     */
    private function returnCategorySlugFromIdCsv(string $id_csv): string
    {
        if ($id_csv === "") {
            $slug = __('all');
        } else {
            // Get localities
            $categories = $this->category->getCategoriesBasedOnIdArray(explode(',', $id_csv));
            // Create a locality slug
            $sting = '';
            foreach ($categories as $category) {
                $sting .= $category->name . '-';
            }
            $slug = Str::slug($sting, '-');
        }
        return $slug;
    }
}

