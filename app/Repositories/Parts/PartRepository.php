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
class PartRepository implements Repository {
    
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
        if (!isset($params['page_size'])) {
            $params['page_size'] = 15;
        }
        
        return Part::paginate($params['page_size']);
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
