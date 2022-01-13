<?php

namespace App\Exceptions\CRM\Email;

/**
 * Class ScrapeRepliesJobFailedException
 *
 * Use this instead of \Exception to throw any kind of scrape replies job failed exception
 *
 * @package App\Exceptions\CRM\Email
 */
class ScrapeRepliesJobFailedException extends \Exception
{

    protected $message = 'An unknown error occurred on scrape replies job!';

}