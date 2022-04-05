<?php

namespace App\Rules\CRM\Leads;

use Illuminate\Contracts\Validation\Rule;
use App\Models\CRM\Leads\Jotform\WebsiteForms;

class JotformEnabled implements Rule
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
        $metadata = json_decode($value);

        if (isset($metadata->formId)) {
            $websiteForm = WebsiteForms::where('jotform_id', $metadata->formId)->first();
            return !is_null($websiteForm) && $websiteForm->status == "ENABLED";
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
        return "JotForm Disabled/Doesn't exist.";
    }
}
