<?php

namespace App\Rules\CRM\Interactions;

use App\Models\Integration\Collector\Collector;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

/**
 * Class ValidCollector
 * @package App\Rules\CRM\Interactions
 */
class ValidCollector implements Rule
{

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $user = Auth::user();
        $collector = Collector::find($value);

        if(empty($collector)) {
            return false;
        }

        return $collector->dealer_id === $user->dealer_id;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'Collector must exist';
    }
}
