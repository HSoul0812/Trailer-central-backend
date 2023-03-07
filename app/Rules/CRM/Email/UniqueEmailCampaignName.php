<?php

namespace App\Rules\CRM\Email;

use App\Models\CRM\Email\Campaign;
use Illuminate\Contracts\Validation\Rule;

/**
 * Class UniqueEmailCampaignName
 * @package App\Rules\CRM\Email
 */
class UniqueEmailCampaignName implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $userId = app('request')->get('user_id');
        $id = app('request')->get('id');

        $query = Campaign::query()->where(['user_id' => $userId, 'campaign_name' => $value]);

        if ($id) {
            $query->where('drip_campaigns_id', '!=', $id);
        }

        return !$query->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'Campaign Name must be unique';
    }
}
