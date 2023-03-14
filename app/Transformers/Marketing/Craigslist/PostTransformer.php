<?php

namespace App\Transformers\Marketing\Craigslist;

use App\Models\Marketing\Craigslist\Post;
use League\Fractal\TransformerAbstract;

/**
 * Class PostTransformer
 * 
 * @package App\Transformers\Marketing\Craigslist
 */
class PostTransformer extends TransformerAbstract
{
    /**
     * @param Post $post
     * @return array
     */
    public function transform(Post $post): array
    {
        return [
            'clapp_post_id' => $post->id,
            'craigslist_id' => $post->clid,
            'profile_id' => $post->profile_id,
            'session_id' => $post->session_id,
            'queue_id' => $post->queue_id,
            'inventory_id' => $post->inventory_id,
            'time' => strtotime($post->added),
            'created_at' => $post->added,
            'updated_at' => $post->updated,
            'drafted_at' => $post->drafted,
            'posted_at' => $post->posted,
            'response' => $post->response,
            'username' => $post->username,
            'title' => $post->title,
            'stock' => $post->stock,
            'price' => $post->price,
            'image' => $post->primary_image,
            'area' => $post->area,
            'subarea' => $post->subarea,
            'category' => $post->category,
            'status' => $post->cl_status,
            'view_url' => $post->view_url,
            'edit_url' => $post->edit_url,
            'manage_url' => $post->manage_url,
            'preview' => $post->preview
        ];
    }
}