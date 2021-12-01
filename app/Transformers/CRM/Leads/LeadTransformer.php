<?php

namespace App\Transformers\CRM\Leads;

use App\Models\CRM\Leads\Lead;
use App\Transformers\CRM\Interactions\InteractionTransformer;
use App\Transformers\Inventory\InventoryTransformer;
use App\Transformers\User\DealerLocationTransformer;
use League\Fractal\TransformerAbstract;
use League\Fractal\Resource\Item;

class LeadTransformer extends TransformerAbstract
{
    public $lazyLoadedIncludes = [
        'otherLeadProperties'
    ];
    protected $defaultIncludes = [
        'preferredLocation',
        'inventoryInterestedIn',
    ];

    protected $availableIncludes = [
        'interactions',
        'otherLeadProperties',
    ];

    protected $inventoryTransformer;

    public function __construct()
    {
        $this->inventoryTransformer = new InventoryTransformer();
    }

    /**
     * Transform Full Lead!
     *
     * @param Lead $lead
     *
     * @return type
     */
    public function transform(Lead $lead)
    {
        $transformedLead =  [
            'id' => $lead->identifier,
            'website_id' => $lead->website_id,
            'dealer_id' => $lead->dealer_id,
            'name' => $lead->full_name,
            'lead_types' => $lead->lead_types,
            'email' => $lead->email_address,
            'phone' => $lead->phone_number,
            'preferred_contact' => $lead->preferred_contact,
            'address' => $lead->full_address,
            'comments' => $lead->comments,
            'note' => $lead->note,
            'referral' => $lead->referral,
            'title' => $lead->title,
            'status' => ($lead->leadStatus) ? $lead->leadStatus->status : Lead::STATUS_UNCONTACTED,
            'source' => ($lead->leadStatus) ? $lead->leadStatus->source : '',
            'next_contact_date' => ($lead->leadStatus) ? $lead->leadStatus->next_contact_date : null,
            'contact_type' => ($lead->leadStatus) ? $lead->leadStatus->contact_type : null,
            'created_at' => $lead->date_submitted,
        ];

        if (!empty($lead->pretty_phone_number)) {
            $transformedLead['phone'] = $lead->pretty_phone_number;
        }

        return $transformedLead;
    }

    public function includePreferredLocation(Lead $lead)
    {
        if (empty($lead->preferred_dealer_location)) {
            return null;
        }

        return $this->item($lead->preferred_dealer_location, new DealerLocationTransformer());
    }

    public function includeInteractions(Lead $lead)
    {
        if (empty($lead->interactions)) {
            return [];
        }

        return $this->collection($lead->interactions, new InteractionTransformer());
    }

    public function includeInventoryInterestedIn(Lead $lead)
    {
        if (empty($lead->units)) {
            return [];
        }

        return $this->collection($lead->units, new InventoryTransformer());
    }

    public function includeOtherLeadProperties(Lead $lead): Item
    {
        return $this->item($lead, function ($lead) {
            return [
                'inventory_id' => $lead->inventory_id,
                'first_name' => $lead->first_name,
                'middle_name' => $lead->middle_name,
                'last_name' => $lead->last_name,
                'is_spam' => $lead->is_spam,
                'contact_email_sent' => $lead->contact_email_sent,
                'adf_email_sent' => $lead->adf_email_sent,
                'last_visited_at' => $lead->last_visited_at,
                'cdk_email_sent' => $lead->cdk_email_sent,
                'metadata' => $lead->metadata,
                'newsletter' => $lead->newsletter,
                'is_from_classifieds' => $lead->is_from_classifieds,
                'dealer_location_id' => $lead->dealer_location_id,
                'is_archived' => $lead->is_archived,
                'unique_id' => $lead->unique_id,
                'customer_id' => $lead->customer_id,
                'ids_exported' => $lead->ids_exported,
                'city' => $lead->city,
                'state' => $lead->state,
                'zip' => $lead->zip,
            ];
        });
    }
}
