<?php

namespace App\Http\Requests\Website\Blog;

use App\Http\Requests\Request;

/**
 * 
 * 
 * @author David A Conway Jr.
 */
class CreatePostRequest extends Request {

    protected $rules = [
        'website_id' => 'required|integer',
        'status' => 'required|in:private,published,scheduled',
        'title' => 'required|string',
        'post_content' => 'nullable|string',
        'meta_keyword' => 'nullable|string',
        'meta_description' => 'nullable|string',
        'entity_config' => 'nullable|string',
    ];
    
}
