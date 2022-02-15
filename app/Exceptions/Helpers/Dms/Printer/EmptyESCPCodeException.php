<?php

namespace App\Exceptions\Helpers\Dms\Printer;

class EmptyESCPCodeException extends \Exception 
{
    protected $message = 'Printer is not configured';
}
