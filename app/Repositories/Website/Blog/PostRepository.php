<?php

namespace App\Repositories\Website\Blog;

use App\Repositories\Website\Blog\PostRepositoryInterface;
use App\Exceptions\NotImplementedException;
use Illuminate\Support\Facades\DB;
use App\Models\Website\Blog\Post;
use Illuminate\Support\Facades\Storage;

class PostRepository implements PostRepositoryInterface {
    
    public function create($params) {
        DB::beginTransaction();

        try {
            $post = Post::create($params);

            if (isset($params['images'])) {
                foreach ($params['images'] as $image) {
                    $isFloorplan = false;
                    if ($image == $params['floorplan']) {
                        $isFloorplan = true;
                    }
                    
                    $this->storeImage($post->id, $image, $isFloorplan);
                }
            }
            
            if (isset($params['files'])) {
                foreach ($params['files'] as $file) {   
                    $this->storeFile($post->id, $file);
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
        
        return $post;
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

    private function storeImage($postId, $image, $isFloorplan) {
        $explodedImage = explode('.', $image);
        $imageExtension = $explodedImage[count($explodedImage) - 1];
        $fileName = 'post-files/'.md5($postId)."/".uniqid().".{$imageExtension}";

        try {
            $imageData = file_get_contents($image, false, stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]));
        } catch (\Exception $ex) {
            throw new ImageNotDownloadedException('Image not accessible: '.$image);
        }

        Storage::disk('s3')->put($fileName, $imageData, 'public');

        PostImage::create([
            'post_id' => $postId,
            'src' => $fileName,
            'is_floorplan' => $isFloorplan ? 1 : 0
        ]);
    }
    
     private function storeFile($postId, $file) {
        $explodedFile = explode('.', $file);
        $fileExtension = $explodedFile[count($explodedFile) - 1];
        
        $fileName = 'post-files/'.md5($postId)."/".uniqid().".{$fileExtension}";

        try {
            $fileData = file_get_contents($file, false, stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]));
        } catch (\Exception $ex) {
            throw new ImageNotDownloadedException('Image not accessible: '.$file);
        }

        Storage::disk('s3')->put($fileName, $fileData, 'public');

        PostFile::create([
            'post_id' => $postId,
            'src' => $fileName,
            'name' => $this->getFilename($file)
        ]);
    }
    
    private function getFilename($file) {
        $explodedFile = explode('/', $file);
        return $explodedFile[count($explodedFile) - 1];
    }
}
