<?php

namespace App\Rules\CRM\Interactions;

use App\Models\CRM\Interactions\InteractionMessage;
use App\Models\CRM\Leads\Lead;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

/**
 * Class ValidInteractionMessage
 * @package App\Rules\CRM\Interactions
 */
class ValidInteractionMessage implements Rule
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

        /** @var InteractionMessage $interactionMessage */
        $interactionMessage = InteractionMessage::query()->find($value);
        if(empty($interactionMessage)) {
            return false;
        }

        if (!isset($interactionMessage->message->lead)) {
            return false;
        }

        /** @var Lead $lead */
        $lead = $interactionMessage->message->lead;

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
        return 'Interaction message must exist';
    }
}
