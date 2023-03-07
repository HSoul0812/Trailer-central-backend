<?php

namespace App\Exceptions\CRM\Interactions;

/**
 * Class InteractionMessageException
 * 
 * Use this instead of \Exception to throw any kind of "unknown exception" on save email interaction
 * 
 * @package App\Exceptions\CRM\Interactions
 */
class SaveEmailInteractionUnknownException extends \Exception
{

    protected $message = 'An unknown exception occurred trying to save email interaction!'; 

}