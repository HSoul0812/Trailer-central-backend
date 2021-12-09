<?php

namespace App\Exceptions\CRM\User;

/**
 * Class MissingNameSalesPersonException
 *
 * Use this instead of \Exception to throw any kind of "missing name on sales person"
 *
 * @package App\Exceptions\CRM\Email
 */
class MissingNameSalesPersonException extends \Exception
{

    protected $message = 'First and Last Name is required for sales person.'; 

}