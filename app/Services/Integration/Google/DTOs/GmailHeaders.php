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
        $clean = self::getCleanHeaders($headers);

        // Split To
        $clean['To-Name'] = '';
        if(isset($clean['To'])) {
            $to = self::splitEmail($clean['To']);
            $clean['To'] = $to['email'];
            $clean['To-Name'] = $to['name'];
        }

        // Split From
        $clean['From-Name'] = '';
        if(isset($clean['From'])) {
            $to = self::splitEmail($clean['From']);
            $clean['From'] = $to['email'];
            $clean['From-Name'] = $to['name'];
        }

        // Fill Headers
        return new self([
            'message_id' => $clean['Message-ID'] ?? '',
            'subject' => $clean['Subject'] ?? '',
            'to_email' => $clean['To'] ?? '',
            'to_name' => $clean['To-Name'] ?? '',
            'from_email' => $clean['From'] ?? '',
            'from_name' => $clean['From-Name'] ?? '',
            'date' => $clean['Date'] ?? ''
        ]);
    }

    /**
     * Get Clean Headers
     * 
     * @param array $headers
     * @return array
     */
    public static function getCleanHeaders($headers) {
        // Loop Headers and Get Results
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

        // Return Cleaned Headers
        return $clean;
    }

    /**
     * Split Email Into Email and Name
     * 
     * @param string $fullEmail
     * @return array{email: string, name: string}
     */
    public static function splitEmail($fullEmail) {
        // Initialize Defaults
        $email = $fullEmail;
        $name = '';

        // Split Full Email Into Email and Name
        $split = explode('<', $email);
        if(!empty($split[1])) {
            $name = $split[0];
            $email = str_replace('>', '', $split[1]);
        }
        

        // Return Cleaned Headers
        return [
            'email' => trim($email),
            'name' => trim($name)
        ];
    }
}