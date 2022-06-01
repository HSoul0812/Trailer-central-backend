<?php

namespace App\Services\Dispatch\Facebook\DTOs;

use App\Models\Marketing\Facebook\Error;
use App\Traits\WithConstructor;
use App\Traits\WithGetter;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Class MarketplaceStep
 * 
 * @package App\Services\Dispatch\Facebook\DTOs
 */
class MarketplaceStep
{
    use WithConstructor, WithGetter;

    /**
     * @const array
     */
    const ACTIONS = [
        'choose', 'create', 'update', 'delete', 'error'
    ];

    /**
     * @const array
     */
    const RESPONSE_VARS = [
        'step' => 'string',
        'action' => 'string',
        'inventoryId' => 'int'
    ];

    /**
     * @const string
     */
    const STEP_ERROR = 'error';


    /**
     * @const string
     */
    const DEFAULT_RESPONSE = 'FB Marketplace Autoposter is currently handling the ' .
                                'unknown step ":step" on the action ":action" for ' .
                                'the inventory ID #:inventoryId.';

    /**
     * @const string
     */
    const DEFAULT_SELECTORS = 'common';


    /**
     * @const string
     */
    const STEP_LOGIN = 'login-fb';

    /**
     * @const string
     */
    const STEP_LOGGED_IN = 'goto-marketing';

    /**
     * @const string
     */
    const STEP_LOGOUT = 'logout-and-close';

    /**
     * @const string
     */
    const STEP_STOP = 'stop-script';

    /**
     * @var string
     */
    private $step;

    /**
     * @var string
     */
    private $action;

    /**
     * @var int
     */
    private $inventoryId;

    /**
     * @var int
     */
    private $marketplaceId;

    /**
     * @var string<json>
     */
    private $logs;

    /**
     * @var string
     */
    private $error;

    /**
     * @var string
     */
    private $message;


    /**
     * Get Response Message for Step
     * 
     * @return string
     */
    public function getResponse(): string {
        // Get Step Log Details
        $text = config('marketing.fb.steps.logs.' . $this->step, self::DEFAULT_RESPONSE);

        // Replace Vars
        $response = $text;
        foreach(self::RESPONSE_VARS as $key => $type) {
            $value = $this->{$key};
            if(empty($value)) {
                $value = $type === 'int' ? '0' : 'N/A';
            }
            $response = str_replace(':' . $key, $value, $response);
        }

        // Return Response Logs
        return $response;
    }

    /**
     * Get Selectors for Step
     * 
     * @return Collection<string>
     */
    public function getSelectors(): Collection {
        // Get Selector Routes to Import
        $selectors = config('marketing.fb.steps.selectors.' . $this->step, self::DEFAULT_SELECTORS);

        // Split Selectors
        $allSelectors = [];
        foreach(explode(",", $selectors) as $selector) {
            // Loop Selectors
            $allSelectors = array_merge($allSelectors, config('marketing.fb.selectors.' . $selector, []));
        }

        // Return All Selectors
        return new Collection($allSelectors);
    }

    /**
     * Get Logs
     * 
     * @return Collection<MarketplaceLog>
     */
    public function getLogs(): Collection {
        // No Logs to Send
        if(empty($this->logs)) {
            return new Collection();
        }

        // Get Logs Array
        $logs = json_decode($this->logs);

        // Loop Logs
        $logging = new Collection();
        foreach($logs as $log) {
            $logging->push(new MarketplaceLog([
                'psr' => $log->loggerType ?? 'debug',
                'message' => is_array($log->logMessage) ? print_r($log->logMessage, true) : $log->logMessage,
                'date' => $log->date
            ]));
        }

        // Return Collection<MarketplaceLog>
        return $logging;
    }

    /**
     * Get Error Type
     * 
     * @return string
     */
    public function getErrorType(): string {
        // Step is Error?
        if($this->step === self::STEP_ERROR) {
            return Error::ERROR_TYPE_DEFAULT;
        }

        // Return Error
        return $this->error;
    }

    /**
     * Calculate Time Using Carbon Based on Error Type
     * 
     * @return string
     */
    public function getExpiryTime(): string {
        // Get Expiry Time Default
        $expires = Error::EXPIRY_HOURS_DEFAULT;

        // Get Based on Error Type
        $error = $this->getErrorType();
        if(isset(Error::EXPIRY_HOURS[$error])) {
            $expires = Error::EXPIRY_HOURS[$error];
        }

        // Calculate Expiry Time From Now
        return Carbon::now()->addHours($expires)->setTimezone('UTC')->toDateTimeString();
    }


    /**
     * Is an Error Step?
     * 
     * @return bool
     */
    public function isError(): bool {
        // Is Step Type an Error?
        if(!empty($this->error) && isset(Error::ERROR_TYPES[$this->error])) {
            return true;
        }

        // Step is Error?
        return ($this->step === self::STEP_ERROR);
    }

    /**
     * Is Login Step?
     * 
     * @return bool
     */
    public function isLogin(): bool
    {
        return ($this->step === self::STEP_LOGIN);
    }

    /**
     * Is Logged In?
     * 
     * @return bool
     */
    public function isLoggedIn(): bool
    {
        return ($this->step === self::STEP_LOGGED_IN);
    }

    /**
     * Is a Logout Step?
     * 
     * @return bool
     */
    public function isLogout(): bool
    {
        return ($this->step === self::STEP_LOGOUT);
    }


    /**
     * Is Stopping Process?
     * 
     * @return bool
     */
    public function isStop(): bool
    {
        return ($this->step === self::STEP_STOP);
    }
}