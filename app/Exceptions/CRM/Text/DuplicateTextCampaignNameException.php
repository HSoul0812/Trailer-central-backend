<?php

namespace App\Exceptions\CRM\Text;

/**
 * Class DuplicateTextCampaignNameException
 *
 * Use this instead of \Exception to throw any kind of "duplicate text campaign name" exception
 *
 * @package App\Exceptions\CRM\Text
 */
class DuplicateTextCampaignNameException extends \Exception
{
    
    protected $message = 'A text campaign already exists with that name!'; 

}