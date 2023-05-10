<?php

namespace App\Transformers\CRM\User;

use App\Models\User\CrmUser;
use League\Fractal\TransformerAbstract;
use App\Repositories\CRM\User\CrmUserRepositoryInterface;

class SettingsTransformer extends TransformerAbstract 
{
    public function transform(CrmUser $crmUser): array
    {
        $data = [
            'user_id' => $crmUser->user_id,
            'price_per_mile' => $crmUser->price_per_mile,
            'email_signature' => $crmUser->email_signature,
            'timezone' => $crmUser->timezone,
            'enable_hot_potato' => $crmUser->enable_hot_potato,
            'disable_daily_digest' => $crmUser->disable_daily_digest,
            'enable_assign_notification' => $crmUser->enable_assign_notification,
            'enable_due_notification' => $crmUser->enable_due_notification,
            'enable_past_notification' => $crmUser->enable_past_notification
        ];

        foreach ($crmUser->settings as $setting) {
            $data[$setting->key] = $setting->value;
        }

        return $data;
    }
}
