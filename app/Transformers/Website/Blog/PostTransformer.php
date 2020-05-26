<?php

namespace App\Transformers\Website\Blog;

use League\Fractal\TransformerAbstract;
use App\Models\Website\Blog\Post;

class PostTransformer extends TransformerAbstract
{
    public function transform(Post $post)
    {
	 return [
             'id' => (int)$post->id,
             'website_id' => (int)$post->website_id,
             'post_content' => $post->post_content,
             'meta_keyword' => $post->meta_keyword,
             'meta_description' => $post->meta_description,
             'title' => $post->title,
             'url_path' => $post->url_path,
             'entity_config' => $post->entity_config,
             'date_created' => $post->date_created,
             'date_updated' => $post->date_updated,
             'date_published' => $post->date_published,
             'status' => $post->status,
             'deleted' => $post->deleted,
         ];
    }
}
