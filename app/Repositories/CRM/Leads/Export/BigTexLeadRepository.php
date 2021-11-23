<?php

namespace App\Repositories\CRM\Leads\Export;

use App\Repositories\CRM\Leads\Export\BigTexLeadRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\CRM\Leads\Lead;

class BigTexLeadRepository implements BigTexLeadRepositoryInterface 
{    
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
                ->where('website_lead.is_spam', Lead::IS_NOT_SPAM)
                ->where('website_lead.date_submitted', '>=', $fromDate)
                ->where('website_lead.is_from_classifieds', Lead::IS_FROM_CLASSIFIEDS)
                ->where('website_lead.bigtex_exported', Lead::IS_BIGTEX_NOT_EXPORTED)
                ->where('inventory.manufacturer', 'LIKE', 'Big Tex Trailers')
                ->groupBy('website_lead.identifier')
                ->chunk($chunkSize, $callable); 
    }

}
