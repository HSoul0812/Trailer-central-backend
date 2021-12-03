<?php

namespace App\Services\Dispatch\Facebook\DTOs;

use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class CommonToken
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
    const DEFAULT_RESPONSE = 'FB Marketplace Autoposter is currently handling the ' .
                                'unknown step ":step" on the action ":action" for ' .
                                'the inventory ID #:inventoryId.';


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
     */
    public function getResponse() {
        // Get Step Log Details
        $text = config('marketing.fb.logs.' . $this->step, self::DEFAULT_RESPONSE);

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
}