<?php

namespace App\Exceptions\CRM\Text;

/**
 * Class DuplicateTextBlastNameException
 *
 * Use this instead of \Exception to throw any kind of "duplicate text blast name" exception
 *
 * @package App\Exceptions\CRM\Text
 */
class DuplicateTextBlastNameException extends \Exception
{
    
    protected $message = 'A text blast already exists with that name!'; 

}