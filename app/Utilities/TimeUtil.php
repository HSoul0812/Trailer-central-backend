<?php


namespace App\Utilities;

/**
 * Class TimeUtil
 * 
 * @package App\Utilities
 */
class TimeUtil
{
    const REQUEST_TIME_FORMAT = 'Y-m-d\TH:i:s';
    const MYSQL_TIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * Convert timeformat of the string expression of datetime.
     * 
     * @param $dateString
     * @param $originTimeFormat
     * @param $targetTimeFormat
     * 
     * @return string
     */
    public static function convertTimeFormat($dateString, $originTimeFormat, $targetTimeFormat)
    {
        $dateString = new \DateTime(date($originTimeFormat, strtotime($dateString)));
        return $dateString->format($targetTimeFormat);
    }
}