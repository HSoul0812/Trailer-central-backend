<?php

namespace App\Services\CRM\Interactions\DTOs;

use App\Traits\WithGetter;

/**
 * Class BuilderStats
 * 
 * @package App\Services\CRM\Interactions\DTOs
 */
class BuilderStats
{
    use WithGetter;


    /**
     * @const string
     */
    const STATUS_SUCCESS = 'success';

    /**
     * @const string
     */
    const STATUS_DUPLICATE = 'duplicate';

    /**
     * @const string
     */
    const STATUS_BOUNCED = 'bounced';

    /**
     * @const string
     */
    const STATUS_SKIPPED = 'skipped';

    /**
     * @const string
     */
    const STATUS_ERROR = 'error';


    /**
     * @var int # Sent Emails
     */
    private $noSent = 0;

    /**
     * @var int # Bounced Emails
     */
    private $noBounced = 0;

    /**
     * @var int # Skipped Emails
     */
    private $noSkipped = 0;

    /**
     * @var int # Duplicate Emails
     */
    private $noDups = 0;

    /**
     * @var int # Errors Trying to Send Emails
     */
    private $noErrors = 0;


    public function __construct(?bool $success = null) {
        if($success !== null) {
            if($success) {
                $this->noSent++;
            } else {
                $this->noErrors++;
            }
        }
    }

    /**
     * Update Stats for Status
     * 
     * @param string $status
     * @param int $increment
     * @return void
     */
    public function updateStats(string $status, int $increment = 1): void {
        switch($status) {
            case self::STATUS_SUCCESS:
                $this->noSent += $increment;
            break;
            case self::STATUS_BOUNCED:
                $this->noBounced += $increment;
            break;
            case self::STATUS_DUPLICATE:
                $this->noDups += $increment;
            break;
            case self::STATUS_ERROR:
                $this->noErrors += $increment;
            break;
        }

        // Mark Skipped!
        if(!in_array($status, [self::STATUS_SUCCESS, self::STATUS_ERROR])) {
            $this->noSkipped += $increment;
        }
    }
}