<?php

namespace App\Models\Parts\Textrail;

use App\Models\Parts\Part as BasePart;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $sku
 * @property string $title
 * @property float $price
 */
class Part extends BasePart
{
    use SoftDeletes;

    protected $table = 'textrail_parts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
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
        'latest_cost',
        'msrp',
        'shipping_fee',
        'use_handling_fee',
        'handling_fee',
        'fulfillment_type',
        'weight',
        'weight_rating',
        'description',
        'qty',
        'show_on_website',
        'is_vehicle_specific',
        'video_embed_code',
        'stock_min',
        'stock_max',
        'is_sublet_specific'
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function type()
    {
        return $this->belongsTo(Type::class);
    }


    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function partAttributes()
    {
        return $this->hasMany(PartAttribute::class, 'part_id', 'id');
    }

    public function searchableAs()
    {
        return env('INDEX_PARTS_TEXTRAIL', 'parts_textrail');
    }

    public function searchable()
    {
        // does nothing
    }

    public function unsearchable()
    {
        // does nothing
    }

    public static function boot(): void
    {
        parent::boot();

        self::disableSearchSyncing();
    }

    public function toSearchableArray(): array
    {
        $array = $this->toArray();

        $array['part_id'] = (string)$this->id;

        $array['brand'] = (string)$this->brand;
        $array['manufacturer'] = (string)$this->manufacturer;
        $array['category'] = (string)$this->category;
        $array['type'] = (string)$this->type;

        // $array['images'] = $this->images->toArray();

        $array['vehicle_specific'] = $this->vehicleSpecifc;

        //
        $array['price'] = (string)$this->modified_cost;

        $array['bins_total_qty'] = $this->qty;

        return $array;
    }

    /**
     * Remove implementation
     *
     * @return void
     */
    public function updateCacheStoreTimes()
    {
        return;
    }
}
