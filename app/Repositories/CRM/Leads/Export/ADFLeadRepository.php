<?php

namespace App\Repositories\CRM\Leads\Export;

use App\Exceptions\NotImplementedException;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadType;

class ADFLeadRepository implements ADFLeadRepositoryInterface
{
    public function create($params)
    {
        throw new NotImplementedException();
    }

    public function delete($params): bool
    {
        throw new NotImplementedException();
    }

    public function get($params)
    {
        throw new NotImplementedException();
    }

    public function update($params): bool
    {
        throw new NotImplementedException();
    }

    public function getAll($params)
    {
        throw new NotImplementedException();
    }

    public function getAllNotExportedChunked(callable $callback, string $fromDate, int $chunkSize = 500): void
    {
        Lead::select('website_lead.*')
                ->join('website', 'website.id', '=', 'website_lead.website_id')
                ->join('lead_email', 'lead_email.dealer_id', '=', 'website_lead.dealer_id')
                ->where('website_lead.is_spam', Lead::IS_NOT_SPAM)
                ->where('website_lead.date_submitted', '>=', $fromDate)
                ->where('website_lead.lead_type', '<>', LeadType::TYPE_JOTFORM)
                ->where('website_lead.lead_type', '<>', LeadType::TYPE_FINANCING)
                ->where('website_lead.lead_type', '<>', LeadType::TYPE_NONLEAD)
                ->groupBy('website_lead.identifier')
                ->chunk($chunkSize, $callback);
    }
}
