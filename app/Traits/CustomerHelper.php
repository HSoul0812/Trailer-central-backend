<?php

namespace App\Traits;

use App\Models\Interactions\Lead;
use App\Models\Interactions\LeadTC;
use App\Models\User\User;

trait CustomerHelper
{
    /**
     * Customer Object Data Fetch
     *
     * @param User $user
     * @param LeadTC $leadTC
     * @return array
     */
    public function getCustomer(User $user, LeadTC $leadTC): array
    {
        if (! empty($user->dealer) && !empty($leadTC)) {
            return [
                'name'  => "{$leadTC->first_name} {$leadTC->last_name}",
                'email' => $leadTC->email_address ?? ''
            ];
        } else {
            return [
                'name'  => '',
                'email' => ''
            ];
        }
    }
}
