<?php

namespace App\Repositories\CRM\Leads\Export;

use App\Repositories\CRM\Leads\Export\IDSLeadRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\Export\LeadEmail;

class IDSLeadRepository implements IDSLeadRepositoryInterface 
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
                ->join('website', 'website.id', '=', 'website_lead.website_id')
                ->join('lead_email', 'lead_email.dealer_id', '=', 'website_lead.dealer_id')
                ->where('website_lead.is_spam', Lead::IS_NOT_SPAM)
                ->where('website_lead.date_submitted', '>=', $fromDate)
                ->where('website_lead.ids_exported', Lead::IS_NOT_IDS_EXPORTED)
                ->where('lead_email.export_format', LeadEmail::EXPORT_FORMAT_IDS)
                ->groupBy('website_lead.identifier')
                ->chunk($chunkSize, $callable); 
    }

}
