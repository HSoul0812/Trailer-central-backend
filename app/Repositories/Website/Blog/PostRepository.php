<?php

namespace App\Repositories\Website\Blog;

use App\Repositories\Website\Blog\PostRepositoryInterface;
use App\Exceptions\NotImplementedException;
use Illuminate\Support\Facades\DB;
use App\Models\Website\Blog\Post;
use Illuminate\Support\Facades\Storage;

class PostRepository implements PostRepositoryInterface {

    private $sortOrders = [
        'title' => [
            'field' => 'title',
            'direction' => 'DESC'
        ],
        '-title' => [
            'field' => 'title',
            'direction' => 'ASC'
        ],
        'date_created' => [
            'field' => 'date_created',
            'direction' => 'DESC'
        ],
        '-date_created' => [
            'field' => 'date_created',
            'direction' => 'ASC'
        ],
        'date_updated' => [
            'field' => 'date_updated',
            'direction' => 'DESC'
        ],
        '-date_updated' => [
            'field' => 'date_updated',
            'direction' => 'ASC'
        ],
        'date_published' => [
            'field' => 'date_published',
            'direction' => 'DESC'
        ],
        '-date_published' => [
            'field' => 'date_published',
            'direction' => 'ASC'
        ]
    ];
    
    public function create($params) {
        DB::beginTransaction();

        try {
            $post = Post::create($params);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception($ex->getMessage());
        }
        
        return $post;
    }

    public function delete($params) {
        DB::beginTransaction();

        try {
            $deleted = Post::delete($params);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception($ex->getMessage());
        }
        
        return $deleted;
    }

    public function get($params) {
        return Post::findOrFail($params['id']);
    }

    public function getAll($params) {
        $query = Post::where('id', '>', 0);

        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }

        if (isset($params['website_id'])) {
            $query = $query->whereIn('website_id', $params['website_id']);
        }

        if (isset($params['id'])) {
            $query = $query->whereIn('id', $params['id']);
        }

        if (isset($params['sort'])) {
            $query = $this->addSortQuery($query, $params['sort']);
        }

        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params) {
        DB::beginTransaction();

        try {
            $post = Post::update($params);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception($ex->getMessage());
        }
        
        return $post;
    }

    private function addSortQuery($query, $sort) {
        if (!isset($this->sortOrders[$sort])) {
            return;
        }

        return $query->orderBy($this->sortOrders[$sort]['field'], $this->sortOrders[$sort]['direction']);
    }
}
