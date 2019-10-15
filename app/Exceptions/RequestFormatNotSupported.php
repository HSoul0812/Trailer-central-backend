<?php

namespace App\Exceptions;

/**
 * @author Eczek
 */
class RequestFormatNotSupported extends \Exception {
    
    protected $message = 'Unsupported Payload Format. Only JSON is supported.'; 
    
}
