<?php

namespace App\Services\Integration\Common\DTOs;

use App\Traits\WithConstructor;
use App\Traits\WithGetter;
use Carbon\Carbon;

/**
 * Class EmailHeaders
 * 
 * @extends App\Services\Integration\Common\DTOs
 */
class EmailHeaders
{
    use WithConstructor, WithGetter;

    /**
     * @var string Message ID From Email
     */
    protected $messageId;

    /**
     * @var string To Email Address
     */
    protected $toEmail;

    /**
     * @var string To Name
     */
    protected $toName;

    /**
     * @var string From Email Address
     */
    protected $fromEmail;

    /**
     * @var string From Name
     */
    protected $fromName;

    /**
     * @var string Subject of Email
     */
    protected $subject;

    /**
     * @var string Date Email Was Sent
     */
    protected $date;


    /**
     * Get Full To
     * 
     * @return string
     */
    public function getFullTo(): string {
        // To Name Exists?
        $to = '';
        if($this->toName) {
            $to = $this->toName;
        }

        // Return To
        if(!empty($to)) {
            return $to . ' <' . $this->toEmail . '>';
        }

        // Return Just To Email
        return $this->toEmail;
    }

    /**
     * Get Full From
     * 
     * @return string
     */
    public function getFullFrom(): string {
        // From Name Exists?
        $from = '';
        if($this->fromName) {
            $from = $this->fromName;
        }

        // Return From
        if(!empty($from)) {
            return $from . ' <' . $this->fromEmail . '>';
        }

        // Return Just From Email
        return $this->fromEmail;
    }

    /**
     * Get Date
     * 
     * @return string
     */
    public function getDate(): string {
        if(!$this->date) {
            // Get New Date
            $this->date = Carbon::now()->setTimezone('UTC')->toDateTimeString();
        }

        // Return Date
        return $this->date;
    }
}