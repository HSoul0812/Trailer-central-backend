<?php

namespace App\Repositories\CRM\Leads;

use App\Repositories\CRM\Leads\StatusRepositoryInterface;
use App\Repositories\CRM\Leads\SourceRepositoryInterface;
use App\Exceptions\NotImplementedException;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadStatus;
use Illuminate\Support\Facades\DB;

class StatusRepository implements StatusRepositoryInterface {

    /**
     * @var SourceRepositoryInterface
     */
    private $sources;

    /**
     * StatusRepository constructor.
     * 
     * @param SourceRepositoryInterface $sources
     */
    public function __construct(SourceRepositoryInterface $sources) {
        $this->sources = $sources;
    }

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

            // Send Lead Source
            $this->sources->create([
                'user_id' => $params['tc_lead_identifier'],
                'source_name' => $params['lead_source']
            ]);
        }

        // Override Status
        if(!isset($params['status'])) {
            $params['status'] = Lead::STATUS_UNCONTACTED;
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

    public function getAll() {
        return [
            [
                'id' => Lead::STATUS_HOT,
                'name' => Lead::STATUS_HOT
            ],
            [
                'id' => Lead::STATUS_COLD,
                'name' => Lead::STATUS_COLD
            ],
            [
                'id' => Lead::STATUS_LOST,
                'name' => Lead::STATUS_LOST
            ],
            [
                'id' => Lead::STATUS_MEDIUM,
                'name' => Lead::STATUS_MEDIUM
            ],
            [
                'id' => Lead::STATUS_NEW_INQUIRY,
                'name' => Lead::STATUS_NEW_INQUIRY
            ],
            [
                'id' => Lead::STATUS_UNCONTACTED,
                'name' => Lead::STATUS_UNCONTACTED
            ],
            [
                'id' => Lead::STATUS_WON,
                'name' => Lead::STATUS_WON
            ],
            [
                'id' => Lead::STATUS_WON_CLOSED,
                'name' => Lead::STATUS_WON_CLOSED
            ]
        ];
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

                // Send Lead Source
                $this->sources->createOrUpdate([
                    'user_id' => $params['tc_lead_identifier'],
                    'source_name' => $params['lead_source']
                ]);
            }

            // Override Status
            if(!isset($params['status'])) {
                $params['status'] = Lead::STATUS_UNCONTACTED;
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
