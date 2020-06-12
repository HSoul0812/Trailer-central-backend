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
        'website_id' => 'required|integer',
        'per_page' => 'integer',
        'sort' => 'in:title,-title,date_created,-date_created,date_modified,-date_modified,date_published,-date_published',
        'status' => 'in:private,published,scheduled',
        'id' => 'array',
        'id.*' => 'integer'
    ];
    
    public function all($keys = null) {
        // Set Default Status!
        var_dump($keys);
        if($keys === null) {
            $keys = array('status' => 'published');
        } elseif(empty($keys['status'])) {
            $keys['status'] = 'published';
        }
        var_dump($keys);

        // Return Result
        $all = parent::all($keys);
        return $all;
    }
}
