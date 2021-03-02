<?php

namespace App\Exceptions\Integration\Facebook;

/**
 * Class MissingCatalogFeedPathException
 *
 * Use this instead of \Exception to throw any kind of error creating catalog feed csv path on Catalog Job
 *
 * @package App\Exceptions\Integration\Facebook
 */
class MissingCatalogFeedPathException extends \Exception
{
    
    protected $message = 'Failed to generate path to CSV feed file on catalog!';

}