<?php

namespace App\Exceptions\Integration\Facebook;

/**
 * Class EmptyCatalogPayloadListingsException
 *
 * Use this instead of \Exception to throw any kind of missing payload on Catalog Job
 *
 * @package App\Exceptions\Integration\Facebook
 */
class EmptyCatalogPayloadListingsException extends \Exception
{
    
    protected $message = 'Could not create catalog CSV, payload listings are missing.';

}