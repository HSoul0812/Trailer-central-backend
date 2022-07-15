<?php

namespace App\Exceptions\Integration\Microsoft;

/**
 * Class CannotReceiveAzureProfileException
 *
 * Use this instead of \Exception to throw any kind of error accessing profile email on Microsoft Azure
 *
 * @package App\Exceptions\Integration\Microsoft
 */
class CannotReceiveAzureProfileException extends \Exception
{
    
    protected $message = 'Error occurred trying to receive Microsoft Azure profile email!';

}