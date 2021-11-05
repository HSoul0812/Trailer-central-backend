<?php

namespace App\Repositories\CRM\Leads;

use App\Exceptions\NotImplementedException;
use App\Models\CRM\Leads\LeadStatus;
use App\Repositories\CRM\Leads\StatusRepositoryInterface;
use App\Services\Common\DTOs\SimpleData;
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
     * @return Collection<SimpleData>
     */
    public function getAll($params) {
        // Return Unique Lead Status
        $statuses = collect([]);
        foreach(LeadStatus::STATUS_ARRAY as $status) {
            $simple = new SimpleData();
            $simple->setIndex($status);
            $simple->setName($status);
            $statuses->push($simple);
        }

        // Return Collection of Status
        return $statuses;
    }

    public function update($params) {
        if (isset($params['id'])) {
            $status = LeadStatus::findOrFail($params['id']);
        } else {
            $status = $this->get(['lead_id' => $params['lead_id']]);
        }

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
            return $this->update($params);
        }

        // Create Status!
        return $this->create($params);
    }
}
