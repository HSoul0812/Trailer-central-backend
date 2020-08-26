<?php

namespace App\Exceptions\CRM\Text;

/**
 * Class NoLeadsTestProcessCampaignException
 *
 * Use this instead of \Exception to throw any kind of "no leads on campaign test" exception
 *
 * @package App\Exceptions\CRM\Text
 */
class NoLeadsTestProcessCampaignException extends \Exception
{
    
    protected $message = 'Cannot proceed with testing process campaign, campaign did not return any leads!';

}