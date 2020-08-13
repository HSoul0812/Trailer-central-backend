<?php

namespace App\Exceptions\Tests;

/**
 * Class MissingTestDealerLocationIdException
 *
 * Use this instead of \Exception to throw any kind of missing test location id exception
 *
 * @package App\Exceptions\Tests
 */
class MissingTestDealerLocationIdException extends \Exception
{

    protected $message = 'Could not retrieve test dealer location ID(s) from environment variables!'; 

}