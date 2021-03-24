<?php

namespace App\Services\Website\Log;

use App\Services\Website\Log\LogServiceInterface;
use Illuminate\Support\Facades\Log;

class LogService implements LogServiceInterface 
{    
    public function log($message) {
        Log::channel('slack')->critical($message);
        return true;
    }
}
