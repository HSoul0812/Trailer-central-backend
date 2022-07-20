<?php

namespace App\Models\Showroom;

use App\Models\Inventory\Category;
use App\Models\Inventory\InventoryFeatureList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;

/**
 * Class Showroom
 * @package App\Models\Showroom
 *
 * @property Category $category
 * @property InventoryFeatureList[] $features
 *
 * @method Builder select($columns = ['*'])
 */
class Showroom extends Model
{
    protected $table = 'showroom';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'parent_id',
        'manufacturer',
        'series',
        'year',
        'model',
        'model_merge',
        'merge_field',
        'engine_type',
        'msrp',
        'length_min',
        'length_max',
        'min_width',
        'max_width',
        'min_height',
        'max_height',
        'length_min_real',
        'length_max_real',
        'width_min_real',
        'width_max_real',
        'height_min_real',
        'height_max_real',
        'pull_type',
        'pull_type_extra',
        'num_battery',
        'num_passenger',
        'description',
        'description_txt',
        'description_html',
        'hitch',
        'image',
        'gvwr',
        'frame',
        'floor',
        'fenders',
        'tongue',
        'suspension',
        'axles',
        'wheels',
        'coupler',
        'jack',
        'tires',
        'ramp',
        'electrical',
        'stalls',
        'configuration',
        'side_doors',
        'window_vents',
        'rear_doors',
        'roof_type',
        'nose_type',
        'living_quarters',
        'colors',
        'notes',
        'dealer_cost',
        'lq_price',
        'is_visible',
        'need_review',
        'brand',
        'video_embed_code',
        'sleeps',
        'slideouts',
        'use_secondary_image',
        'horsepower',
        'engine_size',
        'propulsion',
        'beam',
        'mileage',
        'axle_capacity',
        'payload_capacity',
        'engine',
        'fuel_capacity',
        'electrical_service',
        'length',
        'draft',
        'transom',
        'dead_rise',
        'dry_weight',
        'wet_weight',
        'total_weight_capacity',
        'seating_capacity',
        'hull_type',
        'engine_hours',
        'interior_color',
        'hitch_weight',
        'cargo_weight',
        'fresh_water_capacity',
        'gray_water_capacity',
        'black_water_capacity',
        'furnace_btu',
        'ac_btu',
        'electrical_service',
        'available_beds',
        'number_awnings',
        'awning_size',
        'axle_weight',
        'product_group'
    ];

    /**
     * @return HasOne
     */
    public function category(): HasOne
    {
        return $this->hasOne(Category::class, 'legacy_category', 'type');
    }

    /**
     * @return HasMany
     */
    public function images(): HasMany
    {
        return $this->hasMany(ShowroomImage::class, 'showroom_id');
    }

    /**
     * @return BelongsToMany
     */
    public function features(): BelongsToMany
    {
        return $this->belongsToMany(InventoryFeatureList::class, ShowroomFeature::class, 'showroom_id', 'feature_list_id')
            ->using(ShowroomFeature::class)
            ->withPivot('value');
    }
}
