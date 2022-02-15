<?php

declare(strict_types=1);

namespace App\Rules\User;

use App\Models\CRM\Dms\TaxCalculator;

class ValidTaxCalculator
{

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @param null $dealer_id
     *
     * @return bool
     */
    public function passes(string $attribute, $value, array $parameters = []): bool
    {

        $calculator = TaxCalculator::find($value);

        return $calculator && ($calculator->dealer_id === null || $calculator->dealer_id === (int)$parameters[0]);
    }
}
