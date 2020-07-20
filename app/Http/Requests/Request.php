<?php

namespace App\Http\Requests;

use Dingo\Api\Http\Request as BaseRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\NotImplementedException;
use Illuminate\Database\Eloquent\Model;
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
        
        if ($this->validateObjectBelongsToUser()) {
            $user = Auth::user();
        
            if ($user) {
                if ($this->getObjectIdValue()) {
                    $obj = $this->getObject()->findOrFail($this->getObjectIdValue());
                    if ($user->dealer_id != $obj->dealer_id) {
                        return false;
                    }
                }
                
            }
        }
        
        
        return true;
    }
    
    protected function getObjectIdValue() { 
        return false;
    }
        
    protected function validateObjectBelongsToUser() {
        return false;
    }
}
