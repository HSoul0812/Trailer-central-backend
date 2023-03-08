<?php

namespace App\Services\Marketing\Craigslist\DTOs;

use App\Services\Marketing\Craigslist\DTOs\ClientValidate;
use App\Traits\WithConstructor;
use App\Traits\WithGetter;
use Illuminate\Support\Collection;

/**
 * Class ClientMessage
 * 
 * @package App\Services\Marketing\Craigslist\DTOs
 */
class ClientMessage
{
    use WithConstructor, WithGetter;


    /**
     * @const array<string>
     */
    const MESSAGE_VARS = ['active', 'email', 'elapsed', 'scale'];


    /**
     * @const string
     */
    const LAST_SENT_KEY = 'client:last-warning';


    /**
     * @const string
     */
    const CHECKIN_NOTICE = 'There are currently :active active :email Craigslist clients running at this time.';

    /**
     * @const string
     */
    const WARNING_LOW_CLIENTS = 'WARNING: The number of active Craigslist clients for :email has ' .
            'dropped down to :active, please check to ensure Craigslist posts do not fall too far behind.';

    /**
     * @const string
     */
    const WARNING_ELAPSED = 'WARNING: No :email Craigslist clients have checked in for over :elapsed ' .
            ':scale, please check to ensure Craigslist posts do not fall too far behind.';

    /**
     * @const string
     */
    const WARNING_ALERT = 'ALERT: Its has been over :elapsed :scale since any :email Craigslist ' .
            'clients have checked in! This must be reviewed as soon as possible!';

    /**
     * @const string
     */
    const WARNING_CRITICAL = 'CRITICAL!: Its has been over :elapsed :scale since any :email Craigslist ' .
            'clients have checked in! This must be fixed IMMEDIATELY!';

    
    /**
     * @const array<string>
     */
    const WARNING_LEVELS = [
        'info' => self::CHECKIN_NOTICE,
        'clients' => self::WARNING_LOW_CLIENTS,
        'warning' => self::WARNING_ELAPSED,
        'error' => self::WARNING_ALERT,
        'critical' => self::WARNING_CRITICAL
    ];


    /**
     * @var int
     */
    private $dealerId;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $level;

    /**
     * @var string
     */
    private $message;


    /**
     * Get Varied Level Warning
     * 
     * @param Collection $validation
     * @return ClientMessage
     */
    static public function varied(Collection $validation): ClientMessage {
        // Get Client
        $client = $validation->first();
        foreach($validation as $single) {
            if($single->elapsed() < $client->elapsed()) {
                $client = $single;
            }
        }

        // Discover Client Level
        $message = self::message($client->level, [
            'email'   => $client->email,
            'elapsed' => $client->elapsed(),
            'scale'   => $client->scale()
        ]);

        // Return ClientMessage
        return new self([
            'dealer_id' => $client->dealerId,
            'email'     => $client->email,
            'level'     => $client->level,
            'message'   => $message
        ]);
    }

    /**
     * Get Low Clients Warning
     * 
     * @param Collection $active
     * @return ClientMessage
     */
    static public function warning(Collection $active): ClientMessage {
        // Get Client
        $client = $active->first();
        $level = ClientValidate::CLIENTS_LEVEL;

        // Discover Client Level
        $message = self::message($level, [
            'active' => $active->count(),
            'email'  => $client->email
        ]);

        // Return ClientMessage
        return new self([
            'dealer_id' => $client->dealerId,
            'email'     => $client->email,
            'level'     => ClientValidate::WARNING_LEVELS[0],
            'message'   => $message
        ]);
    }

    /**
     * Get Active Clients Message
     * 
     * @param Collection $active
     * @return ClientMessage
     */
    static public function active(Collection $active): ClientMessage {
        // Get Client
        $client = $active->first();

        // Discover Client Level
        $message = self::message($client->level, [
            'active' => $active->count(),
            'email'  => $client->email
        ]);

        // Return ClientMessage
        return new self([
            'dealer_id' => $client->dealerId,
            'email'     => $client->email,
            'level'     => $client->level,
            'message'   => $message
        ]);
    }

    /**
     * Get Message
     * 
     * @param string $level
     * @param array{string: string} $params
     * @return string
     */
    static public function message(string $level, array $params): string {
        // Get Messages By Warning Level
        $message = self::WARNING_LEVELS[$level];
        if(empty($message)) {
            $levels = array_keys(ClientValidate::WARNING_LEVELS);
            $level = $levels[0];
            $message = self::WARNING_LEVELS[$level];
        }

        // Replace Params
        foreach($params as $key => $value) {
            if(in_array($key, self::MESSAGE_VARS)) {
                $message = str_replace(':' . $key, $value, $message);
            }
        }

        // If Critical, Prepend Username
        $critical = config('marketing.cl.settings.slack.critical');
        if(!empty($critical) && $level === ClientValidate::CRITICAL_LEVEL) {
            $message = '<' . $critical . '> ' . $message;
        }

        // Returned Filled Message
        return $message;
    }
}