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
}
