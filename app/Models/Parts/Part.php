<?php

namespace App\Models\Parts;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use App\Models\Parts\CacheStoreTime;
use Carbon\Carbon;

class Part extends Model
{

    use Searchable;

    protected $table = 'parts_v1';

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

    protected $cacheStores = [
        [
            'dealer_id' => 'dealer_id',
            'type_id' => 'type_id',
            'manufacturer_id' => 'manufacturer_id',
            'category_id' => 'category_id',
            'brand_id' => 'brand_id'
        ],
        [
            'dealer_id' => 'dealer_id',
            'type_id' => 'type_id',
            'manufacturer_id' => 'manufacturer_id',
            'category_id' => 'category_id',
            'brand_id' => null
        ],
        [
            'dealer_id' => 'dealer_id',
            'type_id' => 'type_id',
            'manufacturer_id' => 'manufacturer_id',
            'category_id' => null,
            'brand_id' => null
        ],
        [
            'dealer_id' => 'dealer_id',
            'type_id' => 'type_id',
            'manufacturer_id' => null,
            'category_id' => null,
            'brand_id' => null
        ],
        [
            'dealer_id' => 'dealer_id',
            'type_id' => null,
            'manufacturer_id' => null,
            'category_id' => null,
            'brand_id' => null
        ]
    ];

    public static function boot() {
        parent::boot();

        static::created(function ($part) {

            $part->updateCacheStoreTimes();

        });

        static::updated(function ($part) {

            $part->updateCacheStoreTimes();

        });
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

    // Move to a trait
    public function updateCacheStoreTimes()
    {
        foreach($this->cacheStores as $cache) {
            foreach($cache as $key => $value) {
                if (!empty($value)) {
                    $cache[$key] = $this->{$value};
                }
            }
            $cacheStoreTime = CacheStoreTime::firstOrCreate($cache);
            $cacheStoreTime->update_time = Carbon::now();
        }
    }

    public function brand()
    {
        return $this->belongsTo('App\Models\Parts\Brand');
    }

    public function type()
    {
        return $this->belongsTo('App\Models\Parts\Type');
    }

    public function vendor()
    {
        return $this->belongsTo('App\Models\Parts\Vendor');
    }

    public function category()
    {
        return $this->belongsTo('App\Models\Parts\Category');
    }

    public function manufacturer()
    {
        return $this->belongsTo('App\Models\Parts\Manufacturer');
    }

    public function vehicleSpecific()
    {
        return $this->hasOne('App\Models\Parts\VehicleSpecific');
    }

    public function images()
    {
        return $this->hasMany('App\Models\Parts\PartImage');
    }

    public function bins()
    {
        return $this->hasMany('App\Models\Parts\BinQuantity', 'part_id');
    }
}
