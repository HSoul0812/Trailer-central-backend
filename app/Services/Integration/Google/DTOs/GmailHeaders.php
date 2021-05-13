<?php

namespace App\Services\Integration\Google\DTOs;

use App\Services\Integration\Common\DTOs\EmailHeaders;
use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class GmailHeaders
 * 
 * @package App\Services\Integration\Google\DTOs
 * @extends App\Services\Integration\Common\DTOs\EmailHeaders
 */
class GmailHeaders extends EmailHeaders
{
    use WithConstructor, WithGetter;

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

        // Fill Headers
        return new static([
            'message_id' => $headers['Message-ID'] ?? '',
            'subject' => $headers['Subject'] ?? '',
            'to_email' => $headers['To'] ?? '',
            'from_email' => $headers['From'] ?? '',
            'date' => $headers['Date'] ?? ''
        ]);
    }
}