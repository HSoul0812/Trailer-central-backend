<?php

namespace App\Rules\Dms\Docupilot;

use App\Models\CRM\Dms\Docupilot\DocumentTemplates;
use Illuminate\Contracts\Validation\Rule;

/**
 * Class DocumentTemplateExists
 * @package App\Rules\Dms\Docupilot
 */
class DocumentTemplateExists implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $dealerId = app('request')->get('dealer_id');

        $params = [
            ['template_id', '=', $value],
            ['dealer_id', '=', $dealerId]
        ];

        return DocumentTemplates::where($params)->count() !== 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute not exist in the DB.';
    }
}
