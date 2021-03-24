<?php

namespace App\Exceptions\Tests;

/**
 * Class MissingTestWebsiteIdException
 *
 * Use this instead of \Exception to throw any kind of missing test website id exception
 *
 * @package App\Exceptions\Tests
 */
class MissingTestWebsiteIdException extends \Exception
{

    protected $message = 'Could not retrieve test website ID(s) from environment variables!'; 

}