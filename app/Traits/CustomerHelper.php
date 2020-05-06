<?php

namespace App\Traits;

use App\Models\CRM\Leads\Lead;
use App\Models\User\User;

trait CustomerHelper
{
    /**
     * Customer Object Data Fetch
     *
     * @param User $user
     * @param Lead $lead
     * @return array
     */
    public function getCustomer(User $user, Lead $lead): array
    {
        if (! empty($user->dealer) && !empty($lead)) {
            return [
                'name'  => "{$lead->first_name} {$lead->last_name}",
                'email' => $lead->email_address ?? ''
            ];
        } else {
            return [
                'name'  => '',
                'email' => ''
            ];
        }
    }
}
