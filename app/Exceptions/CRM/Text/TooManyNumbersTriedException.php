<?php

namespace App\Exceptions\CRM\Text;

/**
 * Class TooManyNumbersTriedException
 *
 * Use this instead of \Exception to throw any kind of "too many numbers tried" exception
 *
 * @package App\Exceptions\CRM\Text
 */
class TooManyNumbersTriedException extends \Exception
{
    
    protected $message = 'Failed to use too many different phone numbers, something is seriously wrong here!';

}