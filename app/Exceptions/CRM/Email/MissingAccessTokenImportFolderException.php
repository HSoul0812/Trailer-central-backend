<?php

namespace App\Exceptions\CRM\Email;

/**
 * Class MissingAccessTokenImportFolderException
 *
 * Use this instead of \Exception to throw any kind of missing access token while importing folder requiring a token
 *
 * @package App\Exceptions\CRM\Email
 */
class MissingAccessTokenImportFolderException extends \Exception
{
    
    protected $message = 'Access Token doesn\'t exist while trying to import folder!';

}