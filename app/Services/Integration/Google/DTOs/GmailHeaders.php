<?php

namespace App\Services\Integration\Google\DTOs;

use App\Services\Integration\Common\DTOs\EmailHeaders;

/**
 * Class GmailHeaders
 * 
 * @package App\Services\Integration\Google\DTOs
 * @extends App\Services\Integration\Common\DTOs\EmailHeaders
 */
class GmailHeaders extends EmailHeaders
{
    /**
     * Parse Headers and Return Object
     * 
     * @return GmailHeaders
     */
    public static function parse($headers) {
        // Initialize New Headers Array
        $clean = [];
        foreach($headers as $header) {
            // Clean Name
            if($header->name === 'Message-Id') {
                $header->name = 'Message-ID';
            } elseif($header->name === 'Delivered-To') {
                $header->name = 'To';
            }

            // Add to Array
            $clean[$header->name] = trim($header->value);
        }
        var_dump($clean);
    }
}