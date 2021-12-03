<?php

namespace App\Rules\CRM\Text;

use App\Models\CRM\Interactions\TextLog;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ValidTextsLog implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $user = Auth::user();

        if (empty($user)) {
            return false;
        }

        /** @var TextLog $textLog */
        $textLog = TextLog::query()->find($value);

        if(empty($textLog)) {
            return false;
        }

        if (!isset($textLog->lead)) {
            return false;
        }

        $lead = $textLog->lead;

        if($lead->dealer_id !== $user->dealer_id && $lead->website->dealer_id !== $user->dealer_id) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Text must exist';
    }
}
