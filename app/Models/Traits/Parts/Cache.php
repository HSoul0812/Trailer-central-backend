<?php

namespace App\Models\Traits\Parts;

use App\Models\Parts\CacheStoreTime;
use Illuminate\Support\Facades\Cache as BaseCache;

/**
 * 
 *
 * @author Eczek
 */
trait Cache {
        
    private $allowedParams = [
        'dealer_id' => true,
        'manufacturer_id' => true,
        'type_id' => true,
        'category_id' => true,
        'brand_id' => true
    ];
    
    /**
     * 
     * @param array $params Can contain keys dealer_id
     *                      manufacturer_id, type_id, category_id
     *                      brand_id
     * @return mixed|null
     */
    public function getCacheData($params) {        
        $cacheStoreTime = $this->getCacheStoreTime($params);

        if( !$cacheStoreTime->shouldInvalidate() ) {
            return BaseCache::get($this->getCacheKey());
        }
        BaseCache::forget($this->getCacheKey());        
        return null;
    }
    
    /**
     * 
     * @param array $cacheStoreParams Can contain keys dealer_id
     *                                manufacturer_id, type_id, category_id
     *                                brand_id
     * @param mixed $value
     * @return bool
     */
    public function putCacheData($cacheStoreParams, $value) {
        $cacheStoreTime = CacheStoreTime::firstOrCreate($cacheStoreParams);
        if (empty($cacheStoreTime->update_time)) {
            $cacheStoreTime->update_time = Carbon::now();
        }
        $cacheStoreTime->cache_store_time = Carbon::now();
        $cacheStoreTime->save();
        
        return BaseCache::put($this->getCacheKey(), $value); 
    }
    
    private function getCacheStoreTime($params) {
        
        
    }
    
    protected function getCacheName() {
        return 'parts_cache';
    }
    
    private function getCacheKey() {
        return $this->getCacheName()."-{$this->dealer_id}-{$this->type_id}-{$this->category_id}-{$this->brand_id}";
    }
    
}
