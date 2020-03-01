<?php

namespace App\Models\Parts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class CacheStoreTime extends Model
{ 
    
    public $timestamps = false;
    
    protected $table = 'parts_cache_store_times';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dealer_id',
        'type_id',
        'manufacturer_id',
        'category_id',
        'brand_id',
        'cache_store_time',
        'update_time'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];
    
    public function shouldInvalidate() {
        if ($this->cache_store_time < $this->update_time) {
            return true;
        }
        return false;
    }
            
}
