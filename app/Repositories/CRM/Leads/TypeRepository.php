<?php

namespace App\Repositories\CRM\Leads;

use App\Models\CRM\Leads\LeadType;
use App\Repositories\CRM\Leads\TypeRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TypeRepository implements TypeRepositoryInterface {

    public function create($params): LeadType {
        // Create Lead Type
        return LeadType::create($params);
    }

    public function delete($params) {
        // Delete Lead Type
        return LeadType::where('lead_id', $params['lead_id'])->delete();
    }

    public function get($params): LeadType {
        return LeadType::where('lead_id', $params['lead_id'])
                       ->where('lead_type', $params['lead_type'])->first();
    }

    public function getAll($params): Collection {
        // Return Lead Types
        return LeadType::where('lead_id', $params['lead_id'])->get();
    }

    public function update($params): LeadType {
        $leadType = $this->get($params);

        DB::transaction(function() use (&$leadType, $params) {
            // Update Lead Status
            $leadType->fill($params)->save();
        });

        // Return Full Lead Type Details
        return $leadType;
    }

    /**
     * Get Unique Lead Types
     *
     * @return array
     */
    public function getAllUnique() {
        return [
            [
                'id' => LeadType::TYPE_BUILD,
                'name' => ucfirst(LeadType::TYPE_BUILD)
            ],
            [
                'id' => LeadType::TYPE_CALL,
                'name' => ucfirst(LeadType::TYPE_CALL)
            ],
            [
                'id' => LeadType::TYPE_GENERAL,
                'name' => ucfirst(LeadType::TYPE_GENERAL)
            ],
            [
                'id' => LeadType::TYPE_CRAIGSLIST,
                'name' => ucfirst(LeadType::TYPE_CRAIGSLIST)
            ],
            [
                'id' => LeadType::TYPE_INVENTORY,
                'name' => ucfirst(LeadType::TYPE_INVENTORY)
            ],
            [
                'id' => LeadType::TYPE_TEXT,
                'name' => ucfirst(LeadType::TYPE_TEXT)
            ],
            [
                'id' => LeadType::TYPE_SHOWROOM_MODEL,
                'name' => ucfirst(LeadType::TYPE_SHOWROOM_MODEL)
            ],
            [
                'id' => LeadType::TYPE_RENTALS,
                'name' => ucfirst(LeadType::TYPE_RENTALS)
            ],
            [
                'id' => LeadType::TYPE_FINANCING,
                'name' => ucfirst(LeadType::TYPE_FINANCING)
            ],
            [
                'id' => LeadType::TYPE_SERVICE,
                'name' => ucfirst(LeadType::TYPE_SERVICE)
            ],
            [
                'id' => LeadType::TYPE_TRADE,
                'name' => ucfirst(LeadType::TYPE_TRADE)
            ]
        ];
    }
}
