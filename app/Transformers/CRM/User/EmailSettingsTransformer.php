<?php

namespace App\Transformers\CRM\User;

use App\Services\CRM\User\DTOs\EmailSettings;
use League\Fractal\TransformerAbstract;

class EmailSettingsTransformer extends TransformerAbstract
{
    public function transform(EmailSettings $config): array
    {
        // Return Array
        return [
            'dealer_id' => $config->dealerId,
            'sales_person_id' => $config->salesPersonId,
            'type' => $config->type,
            'method' => $config->method,
            'config' => $config->config,
            'perms' => $config->perms,
            'from' => [
                'email' => $config->fromEmail,
                'name' => $config->fromName
            ],
            'reply' => $config->getReply()
        ];
    }
}