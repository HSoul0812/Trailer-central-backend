<?php

namespace App\Exceptions\CRM\Text;

/**
 * Class NotValidFromNumberException
 * @package App\Exceptions\CRM\Text
 */
class NotValidFromNumberException extends BlastException
{
    protected $message = 'From SMS Number is Invalid';
}
