<?php

namespace App\Exceptions\CRM\Text;

/**
 * Class NoLeadsTestDeliverBlastException
 *
 * Use this instead of \Exception to throw any kind of "no leads on blast test" exception
 *
 * @package App\Exceptions\CRM\Text
 */
class NoLeadsTestDeliverBlastException extends \Exception
{
    
    protected $message = 'Cannot proceed with testing deliver blast, blast did not return any leads!';

}