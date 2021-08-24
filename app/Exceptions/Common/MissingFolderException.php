<?php

namespace App\Exceptions\Common;

/**
 * Class MissingFolderException
 *
 * Use this instead of \Exception to throw any kind of missing folder
 *
 * @package App\Exceptions\Common
 */
class MissingFolderException extends \Exception
{
    
    protected $message = 'Could not find provided folder!';

}