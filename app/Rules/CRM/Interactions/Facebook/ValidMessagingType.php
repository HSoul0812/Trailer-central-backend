<?php

namespace App\Rules\CRM\Interactions\Facebook;

use App\Models\CRM\Interactions\Facebook\Message;
use Illuminate\Contracts\Validation\Rule;

/**
 * Class ValidMessagingType
 * @package App\Rules\CRM\Interactions\Facebook
 */
class ValidMessagingType implements Rule
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
        // Value Exists In Messaging Types?
        return in_array($value, Message::MSG_TYPE_ALL);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Messaging Type must exist';
    }
}
