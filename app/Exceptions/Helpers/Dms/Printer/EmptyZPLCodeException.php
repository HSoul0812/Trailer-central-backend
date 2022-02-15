<?php

namespace App\Exceptions\Helpers\Dms\Printer;

class EmptyZPLCodeException extends \Exception 
{
    protected $message = 'Printer is not configured';
}
