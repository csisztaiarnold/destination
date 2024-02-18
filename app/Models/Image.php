<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Image extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'place_id',
        'filename',
        'extension',
        'title',
        'description',
        'row_order',
        'unique_id',
    ];

    /**
     * Returns the main image
     *
     * @param int $place_id
     *
     * @return mixed
     */
    public static function mainImage(int $place_id)
    {
        return self::where('place_id', $place_id)
            ->where('row_order', 1)
            ->first();
    }

    /**
     * Return images by place ID
     *
     * @param int $place_id
     *
     * @return mixed
     */
    public function getImagesByPlaceId(int $place_id)
    {
        return self::where('place_id', $place_id)->get();
    }

    /**
     * Return an image based on image ID and the submission's unique ID
     *
     * @param int    $image_id
     * @param string $unique_id
     *
     * @return mixed
     */
    public function getImageByIdAndUniqueId(int $image_id, string $unique_id)
    {
        return self::where('id', $image_id)
            ->where('unique_id', $unique_id)
            ->first();
    }

    /**
     * Returns a single image by unique ID
     *
     * @param string $unique_id
     *
     * @return mixed
     */
    public function getSingleImageByUniqueId(string $unique_id)
    {
        return self::where('unique_id', $unique_id)
            ->first();
    }

    /**
     * Return images by post ID
     *
     * @param int $post_id
     *
     * @return mixed
     */
    public function getImagesByPostId(int $post_id)
    {
        return self::where('post_id', $post_id)->get();
    }

    /**
     * Return images by unique ID and sort in an ascending order
     *
     * @param string $unique_id
     *
     * @return mixed
     */
    public function getImagesByUniqueId(string $unique_id)
    {
        return self::where('unique_id', $unique_id)
            ->orderby('order', 'asc')
            ->get();
    }

    /**
     * Deletes an image based on image ID and the submission's unique ID
     *
     * @param int    $image_id
     * @param string $unique_id
     */
    public function deleteImageByIdAndUniqueId(int $image_id, string $unique_id)
    {
        self::where('id', $image_id)->where('unique_id', $unique_id)->delete();
    }

    /**
     * Returns the highest order of images for a submission by the submission's
     * unique ID
     *
     * @param string $unique_id
     *
     * @return mixed
     */
    public function getMaxOrderOfImageByUniqueId(string $unique_id)
    {
        return self::where('place_id', $unique_id)->max('order');
    }

    /**
     * Returns an image to exchange based on its order and the submission's unique
     * ID
     *
     * @param int    $new_order
     * @param string $unique_id
     *
     * @return mixed
     */
    public function getImageToExchangeByUniqueId(int $new_order, string $unique_id)
    {
        return self::where('order', $new_order)
            ->where('unique_id', $unique_id)
            ->first();
    }

    /**
     * Updates the image order
     *
     * @param int    $image_id
     * @param string $place_id
     * @param int    $order
     *
     * @return void
     */
    public function updateImageOrder(int $image_id, string $place_id, int $order): void
    {
        self::where('id', $image_id)
            ->where('place_id', $place_id)
            ->update(['row_order' => $order]);
    }

    /**
     * Reduces order by one where the order of an image is higher than the
     * current order
     * TODO: Check if this is possible with Eloquent instead of a raw statement
     *
     * @param int    $current_order
     * @param string $place_id
     *
     * @return void
     */
    public function reduceOrderWhereOrderIsGreaterThanCurrentOrder(int $current_order, string $place_id): void
    {
        DB::statement(
            "UPDATE `images` SET `row_order` = `row_order`-1 WHERE `place_id` = '" . $place_id . "' AND `row_order` > " . $current_order
        );
    }
}
