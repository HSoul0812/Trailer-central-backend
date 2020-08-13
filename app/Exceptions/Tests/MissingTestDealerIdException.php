<?php

namespace App\Exceptions\Tests;

/**
 * Class MissingTestDealerIdException
 *
 * Use this instead of \Exception to throw any kind of missing test dealer id exception
 *
 * @package App\Exceptions\Tests
 */
class MissingTestDealerIdException extends \Exception
{

    protected $message = 'Could not retrieve test dealer ID from environment variables!'; 

}