<?php

namespace App\Services\CRM\Leads\DTOs;

use App\Models\CRM\Leads\Lead;
use App\Traits\WithConstructor;
use App\Traits\WithGetter;

/**
 * Class IDSLead
 *
 * @package App\Services\CRM\Leads\DTOs
 */
class IDSLead
{
    use WithConstructor, WithGetter;

    /**
     * @var string Set Default Source to IDS
     */
    const DEFAULT_SOURCE = 'IDS';

    /**
     * @var string Set Default Provider to IDS
     */
    const DEFAULT_PROVIDER = 'IDS Import';


    /**
     * @var string Website Source / Domain
     */
    private $source;

    /**
     * @var string First Name
     */
    private $firstName;

    /**
     * @var string First Name
     */
    private $lastName;

    /**
     * @var string Email Address
     */
    private $emailAddress;

    /**
     * @var string Phone Number
     */
    private $phoneNumber;

    /**
     * @var string Comments
     */
    private $comments;


    /**
     * @var string Street Address
     */
    private $addressStreet;

    /**
     * @var string City Address
     */
    private $addressCity;

    /**
     * @var string Region Address
     */
    private $addressRegion;

    /**
     * @var string Zip Address
     */
    private $addressPostal;


    /**
     * @var string Inventory ID
     */
    private $inventoryId;

    /**
     * @var string Inventory Condition
     */
    private $inventoryCondition;

    /**
     * @var string Inventory Stock
     */
    private $inventoryStock;

    /**
     * @var string Inventory Year
     */
    private $inventoryYear;

    /**
     * @var string Inventory Manufacturer
     */
    private $inventoryMfg;

    /**
     * @var string Inventory Brand
     */
    private $inventoryBrand;

    /**
     * @var string Inventory Model
     */
    private $inventoryModel;

    /**
     * @var string Inventory Length
     */
    private $inventoryLength;


    /**
     * Create From Lead
     * 
     * @param Lead $lead
     * @return IDSLead
     */
    static public function fromLead(Lead $lead): IDSLead {
        // Check Inventory
        $inventory = null;
        if(!empty($lead->inventory_id) && !empty($inventory->inventory)) {
            $inventory = $lead->inventory;
        }

        // Map Lead Fields to IDS Lead DTO
        return new self([
            'source' => $lead->website->domain,
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'email_address' => $lead->email_address,
            'phone_number' => $lead->phone_number,
            'comments' => $lead->comments,
            'address_street' => $lead->address,
            'address_city' => $lead->city,
            'address_region' => $lead->region,
            'address_postal' => $lead->zip,
            'inventory_id' => $inventory->inventory_id ?? 0,
            'inventory_condition' => !empty($inventory->condition) ? strtoupper($inventory->condition) : '',
            'inventory_stock' => $inventory->stock ?? '',
            'inventory_year' => $inventory->year ?? '',
            'inventory_mfg' => $inventory->manufacturer ?? '',
            'inventory_brand' => $inventory->brand ?? '',
            'inventory_model' => $inventory->model ?? '',
            'inventory_length' => $inventory->length ?? ''
        ]);
    }


    /**
     * Get Email Params for IDS
     *
     * @return array{source: string,
     *               firstName: string,
     *               lastName: string,
     *               emailAddress: string,
     *               phoneNumber: string,
     *               comments: string,
     *               addressStreet: string,
     *               addressCity: string,
     *               addressRegion: string,
     *               addressPostal: string,
     *               inventoryId: int,
     *               inventoryCondition: string,
     *               inventoryStock: string,
     *               inventoryYear: string,
     *               inventoryMfg: string,
     *               inventoryBrand: string,
     *               inventoryModel: string,
     *               inventoryLength: string}
     */
    public function getEmailVars(): array
    {
        return ['source' => $this->source,
                'firstName' => $this->firstName,
                'lastName' => $this->lastName,
                'emailAdress' => $this->emailAddress,
                'phoneNumber' => $this->phoneNumber,
                'comments' => $this->comments,
                'addressStreet' => $this->addressStreet,
                'addressCity' => $this->addressCity,
                'addressRegion' => $this->addressRegion,
                'addressPostal' => $this->addressZip,
                'inventoryId' => $this->inventoryId,
                'inventoryCondition' => $this->inventoryCondition,
                'inventoryStock' => $this->inventoryStock,
                'inventoryYear' => $this->inventoryYear,
                'inventoryMfg' => $this->inventoryMfg,
                'inventoryBrand' => $this->inventoryBrand,
                'inventoryModel' => $this->inventoryModel,
                'inventoryLength' => $this->inventoryLngth
            ];
    }
}
