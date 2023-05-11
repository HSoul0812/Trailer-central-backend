<?php

declare(strict_types=1);

namespace App\Transformers\Lead;

use App\DTOs\Lead\TcApiResponseLead;
use League\Fractal\TransformerAbstract;

class TcApiResponseLeadTransformer extends TransformerAbstract
{
    public function transform(TcApiResponseLead $lead): array
    {
        return [
             'id' => (int) $lead->id,
             'website_id' => $lead->website_id,
             'name' => $lead->name,
             'dealer_id' => $lead->dealer_id,
             'lead_types' => $lead->lead_types,
             'email_address' => $lead->email_address,
             'phone_number' => $lead->phone_number,
             'comments' => $lead->comments,
             'created_at' => $lead->created_at,
             'zip' => $lead->zip,
         ];
    }
}
