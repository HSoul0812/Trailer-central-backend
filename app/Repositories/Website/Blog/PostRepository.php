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
            if(empty($params['status'])) {
                $params['status'] = 'private';
            }
            if($params['status'] === 'published') {
                $params['date_published'] = date('Y-m-d H:i:s');
            }

            // Add URL Path
            
            if (!isset($params['url_path'])) {
                $params['url_path'] = Post::makeUrlPath($params['title']);
            }            

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
        $post = Post::findOrFail($params['id']);

        DB::transaction(function() use (&$post, $params) {
            $params['deleted'] = '1';

            $post->fill($params)->save();
        });

        return $post;
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

        if (isset($params['status'])) {
            if($params['status'] === 'private') {
                $query = $query->where(function($q) use ($params) {
                    $q->whereNull('status')
                      ->orWhere('status', $params['status']);
                });
            } else {
                $query = $query->where('status', $params['status']);
            }
        }

        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params) {
        $post = Post::findOrFail($params['id']);

        DB::transaction(function() use (&$post, $params) {
            // Set Published?
            if(empty($params['status'])) {
                $params['status'] = 'private';
            }
            if(empty($post['date_published']) && $params['status'] === 'published') {
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

            // Fill Post Details
            $post->fill($params)->save();
        });

        return $post;
    }

    private function addSortQuery($query, $sort) {
        if (!isset($this->sortOrders[$sort])) {
            return;
        }

        return $query->orderBy($this->sortOrders[$sort]['field'], $this->sortOrders[$sort]['direction']);
    }
}
