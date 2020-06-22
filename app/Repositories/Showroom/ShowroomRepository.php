<?php

namespace App\Repositories\Showroom;

use App\Repositories\Showroom\ShowroomRepositoryInterface;
use App\Exceptions\NotImplementedException;
use Illuminate\Support\Facades\DB;
use App\Models\Showroom\Showroom;
use App\Models\Showroom\ShowroomImage;
use App\Models\Showroom\ShowroomFile;
use App\Exceptions\ImageNotDownloadedException;
use Illuminate\Support\Facades\Storage;

class ShowroomRepository implements ShowroomRepositoryInterface {
    
    public function create($params) {
        DB::beginTransaction();

        try {
            $showroom = Showroom::create($params);

            if (isset($params['images'])) {
                foreach ($params['images'] as $image) {
                    $isFloorplan = false;
                    if ($image == $params['floorplan']) {
                        $isFloorplan = true;
                    }
                    
                    $this->storeImage($showroom->id, $image, $isFloorplan);
                }
            }
            
            if (isset($params['files'])) {
                foreach ($params['files'] as $file) {   
                    $this->storeFile($showroom->id, $file);
                }
            }

             DB::commit();
        } catch (\ImageNotDownloadedException $ex) {
            DB::rollBack();
            throw new ImageNotDownloadedException($ex->getMessage());
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception($ex->getMessage());
        }
        
        return $showroom;
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        throw new NotImplementedException;
    }

    public function getAll($params) {
        throw new NotImplementedException;
    }

    public function update($params) {
        throw new NotImplementedException;
    }

    private function storeImage($showroomId, $image, $isFloorplan) {
        $explodedImage = explode('.', $image);
        $imageExtension = $explodedImage[count($explodedImage) - 1];
        $fileName = 'showroom-files/'.md5($showroomId)."/".uniqid().".{$imageExtension}";

        try {
            $imageData = file_get_contents($image, false, stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]));
        } catch (\Exception $ex) {
            throw new ImageNotDownloadedException('Image not accessible: '.$image);
        }

        Storage::disk('s3')->put($fileName, $imageData, 'public');

        ShowroomImage::create([
            'showroom_id' => $showroomId,
            'src' => $fileName,
            'is_floorplan' => $isFloorplan ? 1 : 0
        ]);
    }
    
     private function storeFile($showroomId, $file) {
        $explodedFile = explode('.', $file);
        $fileExtension = $explodedFile[count($explodedFile) - 1];
        
        $fileName = 'showroom-files/'.md5($showroomId)."/".uniqid().".{$fileExtension}";

        try {
            $fileData = file_get_contents($file, false, stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]));
        } catch (\Exception $ex) {
            throw new ImageNotDownloadedException('Image not accessible: '.$file);
        }

        Storage::disk('s3')->put($fileName, $fileData, 'public');

        ShowroomFile::create([
            'showroom_id' => $showroomId,
            'src' => $fileName,
            'name' => $this->getFilename($file)
        ]);
    }
    
    private function getFilename($file) {
        $explodedFile = explode('/', $file);
        return $explodedFile[count($explodedFile) - 1];
    }
}