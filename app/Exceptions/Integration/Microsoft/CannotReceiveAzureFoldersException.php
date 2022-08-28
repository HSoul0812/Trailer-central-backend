<?php

namespace App\Exceptions\Integration\Microsoft;

/**
 * Class CannotReceiveAzureFoldersException
 *
 * Use this instead of \Exception to throw any kind of error accessing email folders on Microsoft Azure
 *
 * @package App\Exceptions\Integration\Microsoft
 */
class CannotReceiveAzureFoldersException extends \Exception
{
    
    protected $message = 'Error occurred trying to receive Microsoft Azure email folders!';

}