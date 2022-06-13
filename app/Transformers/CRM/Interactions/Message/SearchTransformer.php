<?php

namespace App\Transformers\CRM\Interactions\Message;

use League\Fractal\TransformerAbstract;

/**
 * Class InteractionLeadTransformer
 * @package App\Transformers\CRM\Interactions
 */
class SearchTransformer extends TransformerAbstract
{
    /**
     * @param array $interactionMessage
     * @return array
     */
    public function transform(array $interactionMessage): array
    {
        return [
            'id' => $interactionMessage['id'],
            'message_type' => $interactionMessage['message_type'],
            'name' => $interactionMessage['name'],
            'lead_id' => $interactionMessage['lead_id'],
            'dealer_id' => $interactionMessage['dealer_id'],
            'lead_first_name' => $interactionMessage['lead_first_name'],
            'lead_last_name' => $interactionMessage['lead_last_name'],
            'user_name' => $interactionMessage['user_name'],
            'interaction_id' => $interactionMessage['interaction_id'],
            'parent_message_id' => $interactionMessage['parent_message_id'],
            'from_number' => $interactionMessage['from_number'],
            'to_number' => $interactionMessage['to_number'],
            'title' => $interactionMessage['title'],
            'text' => $interactionMessage['text'],
            'from_email' => $interactionMessage['from_email'],
            'to_email' => $interactionMessage['to_email'],
            'from_name' => $interactionMessage['from_name'],
            'to_name' => $interactionMessage['to_name'],
            'date_delivered' => $interactionMessage['date_delivered'],
            'date_sent' => $interactionMessage['date_sent'],
            'hidden' => $interactionMessage['hidden'],
            'is_read' => $interactionMessage['is_read'],
            'unassigned' => $interactionMessage['unassigned'],
            'is_incoming' => $interactionMessage['is_incoming'],
            'files' => $interactionMessage['files'],
        ];
    }
}
