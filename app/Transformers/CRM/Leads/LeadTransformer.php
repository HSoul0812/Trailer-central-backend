<?php

namespace App\Transformers\CRM\Leads;

use App\Helpers\SanitizeHelper;
use App\Models\CRM\Leads\Lead;
use App\Traits\CompactHelper;
use App\Transformers\CRM\Interactions\EmailHistoryTransformer;
use App\Transformers\CRM\Interactions\InteractionTransformer;
use App\Transformers\CRM\Text\TextTransformer;
use App\Transformers\CRM\User\SalesPersonTransformer;
use App\Transformers\Inventory\InventoryTransformer;
use App\Transformers\User\DealerLocationTransformer;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;
use League\Fractal\Resource\Item;
use App\Transformers\CRM\Leads\Facebook\UserTransformer as FbUserTransformer;

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
        'textLogs',
        'otherLeadProperties',
        'leadStatus',
        'inventory',
        'fbUsers',
    ];

    /**
     * @var InventoryTransformer
     */
    protected $inventoryTransformer;

    /**
     * @var FbUserTransformer
     */
    protected $fbUserTransformer;

    /**
     * @var SanitizeHelper
     */
    protected $sanitizeHelper;

    public function __construct()
    {
        $this->inventoryTransformer = new InventoryTransformer();
        $this->fbUserTransformer = new FbUserTransformer();
        $this->sanitizeHelper = new SanitizeHelper();
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
            'identifier' => CompactHelper::expand($lead->identifier),
            'website_id' => $lead->website_id,
            'dealer_id' => $lead->dealer_id,
            'name' => $lead->full_name,
            'lead_types' => $lead->lead_types,
            'email' => is_string($lead->email_address) ? $this->sanitizeHelper->removeBrokenCharacters($lead->email_address) : $lead->email_address,
            'phone' => $lead->phone_number,
            'preferred_contact' => $lead->preferred_contact,
            'address' => $lead->address,
            'full_address' => $lead->full_address,
            'comments' => is_string($lead->comments) ? $this->sanitizeHelper->removeBrokenCharacters($lead->comments) : $lead->comments,
            'note' => is_string($lead->note) ? $this->sanitizeHelper->removeBrokenCharacters($lead->note) : $lead->note,
            'referral' => $lead->referral,
            'title' => $lead->title,
            'status' => ($lead->leadStatus) ? $lead->leadStatus->status : Lead::STATUS_UNCONTACTED,
            'source' => ($lead->leadStatus) ? $lead->leadStatus->source : '',
            'next_contact_date' => ($lead->leadStatus) ? $lead->leadStatus->next_contact_date : null,
            'contact_type' => ($lead->leadStatus) ? $lead->leadStatus->contact_type : null,
            'created_at' => (string) $lead->date_submitted,
            'zip' => $lead->zip,
            'is_archived' => $lead->is_archived,
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

        return $this->collection($lead->interactions, app()->make(InteractionTransformer::class));
    }

    public function includeTextLogs(Lead $lead)
    {
        if (empty($lead->textLogs)) {
            return [];
        }

        return $this->collection($lead->textLogs, new TextTransformer());
    }

    public function includeInventoryInterestedIn(Lead $lead)
    {
        if (empty($lead->units)) {
            return [];
        }

        return $this->collection($lead->units, new InventoryTransformer());
    }

    /**
     * @param Lead $lead
     * @return Item
     */
    public function includeLeadStatus(Lead $lead): ?Item
    {
        if (empty($lead->leadStatus)) {
            return null;
        }

        return $this->item($lead->leadStatus, new LeadStatusTransformer());
    }

    /**
     * @param Lead $lead
     * @return Item
     */
    public function includeInventory(Lead $lead): ?Item
    {
        if (empty($lead->inventory)) {
            return null;
        }

        return $this->item($lead->inventory, $this->inventoryTransformer);
    }

    /**
     * @param Lead $lead
     * @return Collection|array
     */
    public function includeFbUsers(Lead $lead)
    {
        if (empty($lead->fbUsers)) {
            return [];
        }

        return $this->collection($lead->fbUsers, $this->fbUserTransformer);
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
            ];
        });
    }
}
