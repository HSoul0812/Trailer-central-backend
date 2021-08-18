<?php

namespace App\Exceptions\CRM\Email;

/**
 * Class MissingFolderException
 *
 * Use this instead of \Exception to throw any kind of missing folder
 *
 * @package App\Exceptions\CRM\Email
 */
class MissingFolderException extends \Exception
{
    
    protected $message = 'Could not find provided folder!';

}