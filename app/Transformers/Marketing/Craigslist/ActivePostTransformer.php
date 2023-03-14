<?php

namespace App\Transformers\Marketing\Craigslist;

use App\Models\Marketing\Craigslist\ActivePost;
use League\Fractal\TransformerAbstract;

/**
 * Class ActivePostTransformer
 * 
 * @package App\Transformers\Marketing\Craigslist
 */
class ActivePostTransformer extends TransformerAbstract
{
    /**
     * @param ActivePost $post
     * @return array
     */
    public function transform(ActivePost $post): array
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
            'status' => $post->status,
            'view_url' => $post->view_url,
            'edit_url' => $post->edit_url,
            'manage_url' => $post->manage_url,
            'preview' => $post->preview
        ];
    }
}