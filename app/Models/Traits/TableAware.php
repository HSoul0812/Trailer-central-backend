<?php

namespace App\Models\Traits;
use App\Exceptions\NotImplementedException;

trait TableAware {
        
    public static function getTableName() {
        throw new NotImplementedException();
    }
    
}
