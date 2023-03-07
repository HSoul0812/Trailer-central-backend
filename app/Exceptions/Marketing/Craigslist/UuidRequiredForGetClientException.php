<?php

namespace App\Exceptions\Marketing\Craigslist;

/**
 * Class UuidRequiredForGetClientException
 *
 * Use this instead of \Exception to throw any kind of marketplace error uuid is missing for get client exception
 *
 * @package App\Exceptions\Marketing\Craigslist
 */
class UuidRequiredForGetClientException extends \Exception
{

    protected $message = 'Cannot get craigslist client information, uuid was not provided.'; 

}