<?php

namespace App\Rules\Inventory\Floorplan;

use Illuminate\Contracts\Validation\Rule;
use App\Services\Inventory\Floorplan\PaymentServiceInterface;

class PaymentUUIDValid implements Rule
{
    /**
     * @var PaymentServiceInterface
     */
    private $paymentService;

    public function __construct(PaymentServiceInterface $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function validate($attribute, $value, $parameters) {   
        if (!empty($parameters)) {
            $dealerId = current($parameters);            

            return $this->paymentService->validatePaymentUUID($dealerId, $value);
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