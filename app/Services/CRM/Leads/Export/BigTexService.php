<?php

namespace App\Services\CRM\Leads\Export;

use App\Models\CRM\Leads\Lead;
use GuzzleHttp\Client;

class BigTexService implements BigTexServiceInterface
{
    
    /**
     * Field names
     */
    private const FIRST_NAME_FIELD_NAME = 'field_115737042[first]';
    private const LAST_NAME_FIELD_NAME = 'field_115737042[last]';
    private const PREFERRED_CONTACT_FIELD_NAME = 'field_115790789';
    private const EMAIL_FIELD_NAME = 'field_115737043';
    private const ZIP_FIELD_NAME = 'field_115737045[zip]';
    private const PHONE_FIELD_NAME = 'field_115737044';
    private const STORE_ID_FIELD_NAME = 'field_115737048';    
    private const VIN_FIELD_NAME = 'field_115737049';
    private const INVENTORY_ID_FIELD_NAME = 'field_115737050';
    private const LISTING_ID_FIELD_NAME = 'field_115737051';    
    private const FORM_URL_FIELD_NAME = 'field_115737052';
    private const BUSINESS_UNIT_FIELD_NAME = 'field_115737053';
    private const REQUEST_TYPE_FIELD_NAME = 'field_115737054';
    private const FORM_TYPE_FIELD_NAME = 'field_115737055';
    private const COMMENTS_FIELD_NAME = 'field_115737046';
    
    /**
     * Routes
     */
    private const FORM_SUBMIT_ROUTE = 'form/{id}/submission.json';

    /**
     * BigTex form constants
     */
    private const FORM_BUSINESS_UNIT = 'TTCOM';
    private const FORM_REQUEST_TYPE = 'Sales';
    private const FORM_TYPE_NAME = 'InventoryForm';
    
    /**
     *
     * @var Client 
     */
    private $httpClient;
    
    public function __construct()
    {
        $this->httpClient = new Client();
    }
    
    public function export(Lead $lead): bool 
    {
        return $this->formstackExport($lead);
    }

    private function formstackExport(Lead $lead): bool
    {
        $response = $this->httpClient->request('POST', $this->getFormSubmitRoute(), [
            'form_params' => [
                self::FIRST_NAME_FIELD_NAME => $lead->first_name,
                self::LAST_NAME_FIELD_NAME => $lead->last_name ?? '___',
                self::PREFERRED_CONTACT_FIELD_NAME => ucfirst($lead->preferred_contact),
                self::EMAIL_FIELD_NAME => $lead->email_address,
                self::ZIP_FIELD_NAME => $lead->zip,
                self::PHONE_FIELD_NAME => $lead->phone_number,
                self::STORE_ID_FIELD_NAME => $lead->inventory ? $lead->inventory->trailerworld_store_id : '',
                self::VIN_FIELD_NAME => $lead->inventory ? $lead->inventory->vin : '',
                self::INVENTORY_ID_FIELD_NAME => $lead->inventory ? $lead->inventory->inventory_id : '',
                self::LISTING_ID_FIELD_NAME => $lead->inventory ? $lead->inventory->inventory_id : '',
                self::FORM_URL_FIELD_NAME => $lead->inventory ? 'https://www.trailertrader.com' . $lead->inventory->getUrl() : '',
                self::BUSINESS_UNIT_FIELD_NAME => self::FORM_BUSINESS_UNIT,
                self::REQUEST_TYPE_FIELD_NAME => self::FORM_REQUEST_TYPE,
                self::FORM_TYPE_FIELD_NAME => self::FORM_TYPE_NAME,
                self::COMMENTS_FIELD_NAME => $lead->comments
            ],
            'headers' => [
                'Authorization' => 'Bearer ' . config('bigtex.access_token')
            ]
        ]);
        
        if ( $response->getStatusCode() == 201 ) {
            $lead->bigtex_exported = 1;
            $lead->save();
            return true;
        }      
        
        return false;
    }
    
    private function getFormSubmitRoute(): string
    {
        return config('bigtex.api_endpoint') . str_replace('{id}', config('bigtex.form_id'), self::FORM_SUBMIT_ROUTE);
    }
}
