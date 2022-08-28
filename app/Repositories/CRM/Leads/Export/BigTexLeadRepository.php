<?php

namespace App\Repositories\CRM\Leads\Export;

use App\Repositories\CRM\Leads\Export\BigTexLeadRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\Website\Website;
use App\Models\CRM\Leads\Lead;

class BigTexLeadRepository implements BigTexLeadRepositoryInterface 
{    
    private const TRAILER_WORLD_DEALER_ID = 11320; // Needs to be moved to config
    
    public function create($params) {
        throw new NotImplementedException;
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        throw new NotImplementedException;
    }

    public function update($params) {
        throw new NotImplementedException;
    }

    public function getAll($params) {
        throw new NotImplementedException;
    }
    
    public function getAllNotExportedChunked($callable, string $fromDate, int $chunkSize = 500) : void {
        Lead::select('website_lead.*')  
                ->join('inventory', 'website_lead.inventory_id', '=', 'inventory.inventory_id')
                ->where(function($query) {
                    $query->where('website_lead.website_id', Website::TRAILERTRADER_ID)
                            ->orWhere('website_lead.is_from_classifieds', Lead::IS_FROM_CLASSIFIEDS);
                })
                ->where('website_lead.is_spam', Lead::IS_NOT_SPAM)
                ->where('website_lead.date_submitted', '>=', $fromDate)
                ->where('website_lead.bigtex_exported', Lead::IS_BIGTEX_NOT_EXPORTED)
                ->where('inventory.dealer_id', self::TRAILER_WORLD_DEALER_ID)
                ->groupBy('website_lead.identifier')
                ->chunk($chunkSize, $callable); 
    }

}
