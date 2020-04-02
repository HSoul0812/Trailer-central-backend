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
     * @param string $leadId
     * @return array
     */
    public function getCustomer(User $user, LeadTC $leadTC, string $leadId): array
    {
        $name   = '';
        $email  = '';

        if (! empty($user->dealer) && !empty($leadTC)) {
            $name = "{$leadTC->first_name} {$leadTC->last_name}";
            $email = $leadTC->email_address ?? '';
        } else {
            $lead = Lead::whereLeadId($leadId)->first();
            if (! empty($lead) && ! empty($lead->customer)) {
                $name = "{$lead->customer->first_name} {$lead->customer->last_name}";
                $email = $lead->customer->email_address ?? '';
            }
        }

        return [
            'name'  => $name,
            'email' => $email
        ];
    }
}
