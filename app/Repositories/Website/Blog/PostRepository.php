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
            // Set Published?
            if($params['status'] !== 'private') {
                $params['date_published'] = date('Y-m-d H:i:s');
            }

            // Add URL Path
            $params['url_path'] = Post::makeUrlPath($params['title']);

            // Create Post
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
            // Update Post
            $params['deleted'] = '1';
            $deleted = Post::update($params);

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
        $query = Post::where('id', '>', 0)->where('deleted', '=', 0);

        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }

        if (isset($params['website_id'])) {
            $query = $query->where('website_id', $params['website_id']);
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
            // Get Existing Item!
            $post = $this->get(['id' => $params['id']]);

            // Set Published?
            if(empty($post['date_published']) && $params['status'] !== 'private') {
                $params['date_published'] = date('Y-m-d H:i:s');
            }

            // Set URL Path?
            if(empty($post['url_path'])) {
                // Add URL Path
                $title = $params['title'];
                if(empty($title)) {
                    $title = $post['title'];
                }
                $params['url_path'] = Post::makeUrlPath($title);
            }

            // Update Post
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
