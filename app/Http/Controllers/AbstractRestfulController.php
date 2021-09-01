<?php

namespace App\Http\Controllers;

use App\Http\Controllers\RestfulControllerInterface;
use Dingo\Api\Routing\Helpers;
use App\Exceptions\NotImplementedException;

abstract class AbstractRestfulController implements RestfulControllerInterface
{
    use Helpers;
    
    public function __construct() {
        $this->constructRequestBindings();
    }
    
    /**
     * We must register the specific request bindings for our controller here
     * 
     * @throws NotImplementedException
     */
    protected function constructRequestBindings()
    {
        throw new NotImplementedException('Request bindings need to be registered');
    }
}
