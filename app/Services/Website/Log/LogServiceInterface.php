<?php

namespace App\Services\Website\Log;

interface LogServiceInterface {
    
    /**
     * Adds log information
     * 
     * @param string $message
     * @return bool
     */
    public function log($message);
    
}
