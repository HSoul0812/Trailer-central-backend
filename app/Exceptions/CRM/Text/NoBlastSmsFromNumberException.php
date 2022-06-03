<?php

namespace App\Exceptions\CRM\Text;

/**
 * Class NoBlastSmsFromNumberException
 *
 * Use this instead of \Exception to throw any kind of missing blast SMS number-related exception
 *
 * @package App\Exceptions\CRM\Text
 */
class NoBlastSmsFromNumberException extends BlastException
{
    protected $message = 'No From SMS Number Available';
}
