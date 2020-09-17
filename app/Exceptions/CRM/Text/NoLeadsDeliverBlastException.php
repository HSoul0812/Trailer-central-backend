<?php

namespace App\Exceptions\CRM\Text;

/**
 * Class NoLeadsDeliverBlastException
 *
 * Use this instead of \Exception to throw any kind of "no leads on blast" exception
 *
 * @package App\Exceptions\CRM\Text
 */
class NoLeadsDeliverBlastException extends \Exception
{
    
    protected $message = 'Cannot proceed with delivering blast, blast did not return any leads!';

}