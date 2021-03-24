<?php

namespace App\Exceptions\Integration\Facebook;

/**
 * Class FailedCreateTempCatalogCsvException
 *
 * Use this instead of \Exception to throw any kind of error creating catalog csv on Catalog Job
 *
 * @package App\Exceptions\Integration\Facebook
 */
class FailedCreateTempCatalogCsvException extends \Exception
{
    
    protected $message = 'Exceeded memory limit trying to create temporary catalog CSV!';

}