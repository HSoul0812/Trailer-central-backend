<?php

namespace App\Repositories\CRM\Leads;

use App\Exceptions\NotImplementedException;
use App\Models\CRM\Leads\LeadType;
use App\Repositories\CRM\Leads\TypeRepositoryInterface;
use Illuminate\Support\Collection;

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

    public function update($params) {
        throw new NotImplementedException;
    }

    /**
     * Get Unique Lead Types
     *
     * @return array
     */
    public function getAllUnique() {
        // Return Unique Lead Types
        $leadTypes = [];
        foreach(LeadType::TYPE_ARRAY as $type) {
            $leadTypes[] = [
                'id' => $type,
                'name' => ucfirst($type)
            ];
        }
        return $leadTypes;
    }
}
