<?php

namespace App\Repositories\Parts;

use App\Repositories\Repository;
use App\Models\Parts\Part;
use App\Models\Parts\PartImage;
use Illuminate\Support\Facades\Storage;

/**
 *  
 * @author Eczek
 */
class PartRepository implements PartRepositoryInterface {
    
    public function create($params) {
        $part = Part::create($params);
        
        if (isset($params['images'])) {
            foreach ($params['images'] as $imageUrl) {
                $this->storeImage($part->id, $imageUrl);
            }
        }
        
        return $part;
    }

    public function delete($params) {
        $part = Part::findOrFail($params['id']);
        return $part->delete();
    }

    public function get($params) {
        return Part::findOrFail($params['id']);
    }

    public function getAll($params) {   
        
        $query = Part::where('id', '>', 0);
        
        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }
        
        if (isset($params['type_id'])) {
            $query = $query->whereIn('type_id', $params['type_id']);
        }
        
        if (isset($params['category_id'])) {
            $query = $query->whereIn('category_id', $params['category_id']);
        }
        
        if (isset($params['manufacturer_id'])) {
            $query = $query->whereIn('manufacturer_id', $params['manufacturer_id']);
        }
        
        if (isset($params['brand_id'])) {
            $query = $query->whereIn('brand_id', $params['brand_id']);
        }
        
        if (isset($params['subcategory'])) {           
            $query = $query->where('subcategory', 'LIKE', '%'.$params['subcategory'].'%');
        }
        
        if (isset($params['sku'])) {           
            $query = $query->where('sku', 'LIKE', '%'.$params['sku'].'%');
        }
        
        if (isset($params['price_min']) && isset($params['price_max'])) {
            $query = $query->whereBetween('price', [$params['price_min'], $params['price_max']]);
        } else if (isset($params['price'])) {
            $query = $query->where('price', $params['price']);
        }         
        
        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params) {
        $part = Part::findOrFail($params['id']);
        $part->fill($params);
        if ($part->save()) {
            if (isset($params['images'])) {
                $part->images()->delete();
                foreach($params['images'] as $imageUrl) {
                    $this->storeImage($part->id, $imageUrl);
                }
            }
            return $part;
        }       
    }
    
    private function storeImage($partId, $imageUrl) {
        $explodedImage = explode('.', $imageUrl);
        $imageExtension = $explodedImage[count($explodedImage) - 1];
        $fileName = md5($partId)."/".uniqid().".{$imageExtension}";
        Storage::disk('s3')->put($fileName, file_get_contents($imageUrl), 'public');
        $s3ImageUrl = Storage::disk('s3')->url($fileName);

        PartImage::create([
            'part_id' => $partId,
            'image_url' => $s3ImageUrl
        ]);
    }

}
