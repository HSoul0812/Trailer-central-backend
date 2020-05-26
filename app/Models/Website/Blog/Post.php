<?php

namespace App\Models\Website\Blog;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Carbon\Carbon;

/**
 * Class Blog Post
 *
 * @package App\Models\Website\Blog
 */
class Post extends Model
{

    use Searchable;

    protected $table = 'website_blog';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dealer_id',
        'vendor_id',
        'vehicle_specific_id',
        'manufacturer_id',
        'brand_id',
        'type_id',
        'category_id',
        'qb_id',
        'subcategory',
        'title',
        'alternative_part_number',
        'sku',
        'price',
        'dealer_cost',
        'msrp',
        'weight',
        'weight_rating',
        'description',
        'qty',
        'show_on_website',
        'is_vehicle_specific',
        'video_embed_code',
        'stock_min',
        'stock_max',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public static function boot() {
        parent::boot();
    }

    public function searchableAs()
    {
        return env('PARTS_ALGOLIA_INDEX', '');
    }

    public function toSearchableArray()
    {
        $array = $this->toArray();

        $array['brand'] = (string)$this->brand;
        $array['manufacturer'] = (string)$this->manufacturer;
        $array['category'] = (string)$this->category;
        $array['type'] = (string)$this->type;

        $array['images'] = $this->images->toArray();
        $array['vehicle_specific'] = $this->vehicleSpecifc;

        return $array;
    }
}
