<?php

namespace App\Http\Requests\Website\Blog;

use App\Http\Requests\Request;

/**
 * 
 * 
 * @author David A Conway Jr.
 */
class GetPostsRequest extends Request {
    
    protected $rules = [
        'per_page' => 'integer',
        'sort' => 'in:relevance,title,-title,length,-length,date_created,-date_created,date_modified,-date_modified,date_published,-date_published',
        'dealer_id' => 'array',
        'dealer_id.*' => 'integer',
        'status' => 'in:private,published,scheduled',
        'id' => 'array',
        'id.*' => 'integer'
    ];
    
    public function all($keys = null) {
        $all = parent::all($keys);
        return $all;
    }
}
