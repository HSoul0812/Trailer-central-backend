<?php

namespace App\Repositories\CRM\Leads;

use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\CRM\Leads\Lead;
use App\Models\User\User;
use App\Models\CRM\Leads\LeadStatus;

class LeadRepository implements LeadRepositoryInterface {
    
    public function create($params) {
        throw new NotImplementedException;
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        throw new NotImplementedException;
    }

    public function getAll($params) {
        $query = Lead::where('identifier', '>', 0);
        
        if (isset($params['dealer_id'])) {
            $query = $query->where('dealer_id', $params['dealer_id']);
        }
        
        if (!isset($params['per_page'])) {
            $params['per_page'] = 15;
        }
        
        return $query->paginate($params['per_page'])->appends($params);
    }

    public function update($params) {
        throw new NotImplementedException;
    }

    public function getLeadStatusCountByDealer($dealerId) {
        $user = User::findOrFail($dealerId);
        
        $wonLeads = $user->leads()
                ->join(LeadStatus::getTableName(), Lead::getTableName().'.identifier', '=', LeadStatus::getTableName().'.tc_lead_identifier')
                ->whereIn(LeadStatus::getTableName().'.status', [Lead::STATUS_WON, Lead::STATUS_WON_CLOSED])
                ->where(Lead::getTableName().'.is_archived', Lead::NOT_ARCHIVED)
                ->count();
        
        $openLeads = $user->leads()
                        ->join(LeadStatus::getTableName(), Lead::getTableName().'.identifier', '=', LeadStatus::getTableName().'.tc_lead_identifier')
                        ->whereNotIn(LeadStatus::getTableName().'.status', [Lead::STATUS_WON, Lead::STATUS_WON_CLOSED, Lead::STATUS_LOST])
                        ->where(Lead::getTableName().'.is_archived', Lead::NOT_ARCHIVED)
                        ->count();
        
        $lostLeads = $user->leads()
                        ->join(LeadStatus::getTableName(), Lead::getTableName().'.identifier', '=', LeadStatus::getTableName().'.tc_lead_identifier')
                        ->where(LeadStatus::getTableName().'.status', Lead::STATUS_LOST)
                        ->where(Lead::getTableName().'.is_archived', Lead::NOT_ARCHIVED)
                        ->count();
        
        $hotLeads = $user->leads()
                        ->join(LeadStatus::getTableName(), Lead::getTableName().'.identifier', '=', LeadStatus::getTableName().'.tc_lead_identifier')
                        ->where(LeadStatus::getTableName().'.status', Lead::STATUS_HOT)
                        ->where(Lead::getTableName().'.is_archived', Lead::NOT_ARCHIVED)
                        ->count();
        
        return [
            'won' => $wonLeads,
            'open' => $openLeads,
            'lost' => $lostLeads,
            'hot' => $hotLeads
        ];
    }
    
    

}
