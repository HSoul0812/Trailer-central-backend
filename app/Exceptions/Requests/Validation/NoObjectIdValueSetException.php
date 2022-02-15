<?php

namespace App\Exceptions\Requests\Validation;

/**
 * Thrown when theres no object id set when validation that a given record belongs to a user
 */
class NoObjectIdValueSetException extends \Exception {
    
    protected $message = 'Object ID value needs to be set to validate object belongs to user';
    
}
