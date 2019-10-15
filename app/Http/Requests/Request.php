<?php

namespace App\Http\Requests;

use Dingo\Api\Http\Request as BaseRequest;
use Illuminate\Support\Facades\Validator;

/**
 *  
 * @author Eczek
 */
class Request extends BaseRequest {
    
    /**
     * Rules to validate
     * 
     * @var array
     */
    protected $rules = [];
        
    public function validate() {
        $validator = Validator::make($this->all(), $this->rules);

        if ($validator->fails()) {
            throw new \Dingo\Api\Exception\ResourceException("Validation Failed", $validator->errors());
        }
        
        return true;
    }
}
