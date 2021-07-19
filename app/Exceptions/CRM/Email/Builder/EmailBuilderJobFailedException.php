<?php

namespace App\Exceptions\CRM\Email\Builder;

/**
 * Class EmailBuilderJobFailedException
 *
 * Use this instead of \Exception to throw any kind of full email builder job failed exception
 *
 * @package App\Exceptions\CRM\Email\Builder
 */
class EmailBuilderJobFailedException extends \Exception
{

    protected $message = 'An unknown error occurred processing full email builder job!';

}