<?php

namespace App\Rules\CRM\Email;

use App\Models\CRM\Email\Blast;

/**
 * Class UniqueEmailBlastName
 * @package App\Rules\CRM\Email
 */
class UniqueEmailBlastName
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

        $query = Blast::query()->where(['user_id' => $userId, 'campaign_name' => $value]);

        if ($id) {
            $query->where('email_blasts_id', '!=', $id);
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
        return 'Blast Name must be unique';
    }
}
