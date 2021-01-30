<?php

namespace App\Repositories\CRM\Leads;

use App\Exceptions\NotImplementedException;
use App\Models\CRM\Leads\LeadSource;
use App\Repositories\CRM\Leads\SourceRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SourceRepository implements SourceRepositoryInterface {

    use SortTrait;

    public function create($params): LeadSource {
        // Create Lead Source
        return LeadSource::create($params);
    }

    public function delete($params) {
        throw new NotImplementedException;
    }

    public function get($params): LeadSource {
        return LeadSource::where('user_id', $params['user_id'])
                         ->where('source_name', $params['source_name'])->first();
    }

    public function getAll($params): Collection {
        // Set User ID By Default
        if(!isset($params['user_id'])) {
            $params['user_id'] = 0;
        }

        // Return Lead Sources
        return LeadSource::where('user_id', $params['user_id'])
                         ->orderBy('lead_source_id', 'ASC')
                         ->get();
    }

    public function update($params): LeadSource {
        $source = $this->get($params);

        DB::transaction(function() use (&$source, $params) {
            // Update Lead Status
            $source->fill($params)->save();
        });

        // Return Full Lead Source Details
        return $source;
    }

    /**
     * Create or Update Lead Source
     * 
     * @param array $params
     * @return LeadSource
     */
    public function createOrUpdate($params): LeadSource {
        // Source Exists?
        $source = $this->find($params);
        if(!empty($source->user_id)) {
            return $source;
        }

        // Source Exists?
        if(!empty($source->id)) {
            return $this->update($params);
        }

        // Create Source
        return $this->create($params);
    }

    /**
     * Find Lead Source
     * 
     * @param array $params
     * @return LeadSource || null
     */
    public function find($params) {
        // Find Defaults
        $default = LeadSource::where('source_name', $params['source_name'])
                             ->where('user_id', 0)->first();

        // Find on Dealer
        $source = LeadSource::where('source_name', $params['source_name'])
                            ->where('user_id', $params['user_id'])->first();

        // Default Exists?
        if(!empty($default->lead_source_id)) {
            // Source Doesn't Exist on Dealer?
            if(empty($source->lead_source_id)) {
                return $default;
            }

            // It DOES?!
            return $source;
        }

        // Doesn't Exist
        return null;
    }
}
