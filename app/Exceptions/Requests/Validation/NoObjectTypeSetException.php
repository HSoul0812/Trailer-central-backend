<?php

namespace App\Exceptions\Requests\Validation;

/**
 * Thrown when theres no object type set when validation that a given record belongs to a user
 */
class NoObjectTypeSetException extends \Exception {
    
    protected $message = 'Object Type needs to be set to validate object belongs to user';
    
}
