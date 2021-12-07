<?php

namespace App\Services\Dispatch\Facebook\DTOs;

use App\Traits\WithConstructor;
use App\Traits\WithGetter;
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
        'choose', 'create', 'update', 'delete'
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
     * @var string<json>
     */
    private $logs;


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
        var_dump($selectors);
        var_dump(explode(",", $selectors));

        // Split Selectors
        $allSelectors = new Collection();
        foreach(explode(",", $selectors) as $selector) {
            // Loop Selectors
            var_dump($selector);
            $allSelectors->merge(config('marketing.fb.selectors.' . $selector));
        }

        // Return All Selectors
        return $allSelectors;
    }

    /**
     * Get Logs
     * 
     * @return Collection<MarketplaceLog>
     */
    public function getLogs(): Collection {
        // Get Logs Array
        $logs = json_decode($this->logs);

        // Loop Logs
        $logging = new Collection();
        foreach($logs as $log) {
            $logging->push(new MarketplaceLog([
                'psr' => $log->loggerName,
                'message' => $log->logMessage,
                'date' => $log->date
            ]));
        }

        // Return Collection<MarketplaceLog>
        return $logging;
    }

    /**
     * Is an Error Step?
     * 
     * @return bool
     */
    public function isError(): bool {
        return ($this->step === self::STEP_ERROR);
    }
}