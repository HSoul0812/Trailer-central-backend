<?php

namespace App\Rules\CRM\Text;

use App\Models\CRM\Text\Blast;
use Illuminate\Contracts\Validation\Rule;

/**
 * Class UniqueTextBlastCampaignName
 * @package App\Rules\CRM\Text
 */
class UniqueTextBlastCampaignName implements Rule
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
        $userId = app('request')->get('user_id');
        $id = app('request')->get('id');

        $query = Blast::query()->where(['user_id' => $userId, 'campaign_name' => $value]);

        if ($id) {
            $query->where('id', '!=', $id);
        }

        return !$query->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Campaign Name must be unique';
    }
}
