<?php

namespace App\Repositories\CRM\Leads;

use App\Repositories\CRM\Leads\StatusRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\CRM\Leads\LeadStatus;
use Illuminate\Support\Facades\DB;

class StatusRepository implements StatusRepositoryInterface {

    public function create($params) {
        // Override Lead ID
        if (isset($params['lead_id'])) {
            $params['tc_lead_identifier'] = $params['lead_id'];
            unset($params['lead_id']);
        }

        // Override Fixes
        if (isset($params['lead_source'])) {
            $params['source'] = $params['lead_source'];
            unset($params['lead_source']);
        }

        // Override Status
        if(!isset($params['status'])) {
            $params['status'] = LeadStatus::STATUS_UNCONTACTED;
        }
        if (isset($params['lead_status'])) {
            $params['status'] = $params['lead_status'];
        }

        // Contact Type Not Set?
        if(!isset($params['contact_type'])) {
            $params['contact_type'] = LeadStatus::TYPE_CONTACT;
        }

        // Create Lead Status
        return LeadStatus::create($params);
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params) {
        return LeadStatus::where('tc_lead_identifier', $params['lead_id'])->first();
    }

    /**
     * Get All Statuses
     * 
     * @param array $params
     * @return array
     */
    public function getAll($params) {
        // Return Unique Lead Status
        $statuses = [];
        foreach(LeadStatus::STATUS_ARRAY as $status) {
            $statuses[] = [
                'id' => $status,
                'name' => $status
            ];
        }
        return collect($statuses);
    }

    public function update($params) {
        $status = $this->get(['lead_id' => $params['lead_id']]);

        DB::transaction(function() use (&$status, $params) {
            // Override Lead ID
            if (isset($params['lead_id'])) {
                $params['tc_lead_identifier'] = $params['lead_id'];
                unset($params['lead_id']);
            }

            // Override Fixes
            if (isset($params['lead_source'])) {
                $params['source'] = $params['lead_source'];
                unset($params['lead_source']);
            }

            // Override Status
            if(!isset($params['status'])) {
                $params['status'] = LeadStatus::STATUS_UNCONTACTED;
            }
            if (isset($params['lead_status'])) {
                $params['status'] = $params['lead_status'];
            }

            // Contact Type Not Set?
            if(!isset($params['contact_type'])) {
                $params['contact_type'] = LeadStatus::TYPE_CONTACT;
            }

            // Update Lead Status
            $status->fill($params)->save();

        });

        // Return Full Lead Status Details
        return $status;
    }

    /**
     * Create or Update Lead Status
     * 
     * @param array $params
     * @return LeadStatus
     */
    public function createOrUpdate($params) {
        // Status Exists?
        $status = $this->get($params);

        // Status Exists?
        if(!empty($status->id)) {
            return $this->update($status);
        }

        // Create Status!
        return $this->create($status);
    }
}
