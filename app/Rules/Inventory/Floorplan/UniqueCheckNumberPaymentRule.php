<?php

namespace App\Rules\Inventory\Floorplan;

use App\Models\CRM\Dms\Quickbooks\Expense;
use App\Services\Inventory\Floorplan\PaymentService;
use Illuminate\Contracts\Validation\Rule;

class UniqueCheckNumberPaymentRule implements Rule
{
    /**
     * @var int
     */
    private $dealerId;

    /**
     * @param int $dealerId
     */
    public function __construct(int $dealerId)
    {
        $this->dealerId = $dealerId;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     *
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $paymentService = resolve(PaymentService::class);

        return !$paymentService->checkNumberExists($this->dealerId, $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'The :attribute already exists.';
    }
}
