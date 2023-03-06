<?php

namespace App\Services\Dispatch\Craigslist\DTOs;

use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class ClappError
 * 
 * @package App\Services\Dispatch\Craigslist\DTOs
 */
class ClappError
{
    use WithConstructor, WithGetter;

    /**
     * @const array{string: string}
     */
    const ERRORS_REPLACE = [
        'step' => 'step',
        'error' => 'message',
        'sessionId' => 'session_id',
        'clientName' => 'client_name',
    ];


    /**
     * @const string
     */
    const ERROR_PUBLIC = 'Your session has stopped due to an unexpected error. ' .
        'If problems persist or if you are unable to post contact support.';

    /**
     * @const string
     */
    const ERROR_GENERIC = 'An error was received: <error> during the step: ' .
        '<step> on session #<sessionId> on the PC <clientName>';

    /**
     * @const string
     */
    const ERROR_LOGIN = 'FATAL ERROR: We could not log in! ' .
        'The following error was returned: <error> during the step: ' .
        '<step> on session #<sessionId> on the PC <clientName>';


    /**
     * @const array{string: string}
     */
    const ERROR_MESSAGES = [
        'pending-billing'   => 'Waiting for billing...',
        'login-invalid'     => 'Invalid username / password on the profile you are trying to post to.',
        'login-error'       => 'Error occurred trying to log in to craigslist: <error>',
        'login-fatal'       => 'Fatal error occurred trying to login: <error>',
        'login-verify'      => 'Failed to login, verification required: <error>',
        'invalid-data'      => 'Posting blocked, but no reason given by Craigslist.',
        'invalid-blocked'   => 'Posting blocked due to <error> in post.',
        'invalid-expired'   => 'The post trying to be edited has already expired.',
        'invalid-flagged'   => 'The post trying to be edited has been flagged as spam for removal.',
        'invalid-deleted'   => 'The post trying to be edited has already been deleted.',
        'wrong-link'        => 'Detected wrong link page, Craigslist won\'t let us edit this post!'
    ];


    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $state;

    /**
     * @var string
     */
    private $textStatus;


    /**
     * Fill ClappError With Details Based on Error Message
     * 
     * @param string $error
     * @param string $msg
     * @param array $params
     * @return ClappError
     */
    public static function fill(string $error, array $params) {
        // Get Standard
        $status = 'error';
        $state = $error;

        // Get Pending Billing
        if($error === 'pending-billing') {
            $status = $error;
            $state = 'billing-add-funds';
        }

        // Return Error
        return new ClappError([
            'status' => $status,
            'state' => $state,
            'text_status' => self::getError($error, $params)
        ]);
    }

    /**
     * Get Error Text And Fill Params
     * 
     * @param string $error
     * @return string
     */
    public static function getError(string $error, array $params): string {
        // Override Error Message Exists?!
        if(!empty($params['text_status'])) {
            return $params['text_status'];
        }

        // Get Default Error Message
        $msg = self::ERROR_MESSAGES[$error] ?? null;
        if(empty($msg)) {
            if(strpos($error, "login-") === 0) {
                $msg = self::ERROR_LOGIN;
            } else {
                $msg = self::ERROR_GENERIC;
            }
        }

        // Replace Fields We Have Available
        foreach(self::ERRORS_REPLACE as $replace => $value) {
            $param = $params[$value] ?? '??';
            $msg = str_replace('<' . $replace . '>', $param, $msg);
        }

        // Return Final Message
        return $msg;
    }
}