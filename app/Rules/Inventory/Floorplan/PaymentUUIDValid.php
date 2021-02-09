<?php

namespace App\Rules\Inventory\Floorplan;

use Illuminate\Contracts\Validation\Rule;
use App\Services\Common\RedisServiceInterface;

class PaymentUUIDValid implements Rule
{
    /**
     * @var RedisServiceInterface
     */
    private $redisService;

    public function __construct(RedisServiceInterface $redisService)
    {
        $this->redisService = $redisService;
    }

    public function validate($attribute, $value, $parameters) {   
        if (!empty($parameters)) {
            $dealerId = current($parameters);            

            return $this->redisService->validatePaymentUUID($dealerId, $value);
        }
        
        return false;        
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The payment uuid is invalid.';
    }
}