<?php

namespace App\Repositories\CRM\Leads;

use App\Exceptions\NotImplementedException;
use App\Models\CRM\Leads\LeadSource;
use App\Repositories\CRM\Leads\SourceRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SourceRepository implements SourceRepositoryInterface {

    public function create($params): LeadSource {
        // Create Lead Source
        return LeadSource::create($params);
    }

    /**
     * Soft Delete by ID
     */
    public function delete($params) {

        return LeadSource::findOrFail($params['id'])
            ->update(['deleted' => 1]);
    }

    public function get($params): LeadSource {
        return LeadSource::where('user_id', $params['user_id'])
                         ->where('source_name', $params['source_name'])->first();
    }

    public function getAll($params): Collection {
        // User ID Set?
        if(empty($params['user_id'])) {
            return LeadSource::where('user_id', 0)
                         ->orderBy('lead_source_id', 'ASC')
                         ->get();
        }

        // Get Default and Dealer Sources
        $sources = LeadSource::orWhere('user_id', $params['user_id'])
                             ->orWhere('user_id', 0)
                             ->orderBy('lead_source_id', 'ASC')
                             ->cursor();

        // Loop Sources
        $names = [];
        $overrides = [];
        foreach($sources as $source) {
            // Get Source ID
            $sourceId = !empty($source->parent_id) ? $source->parent_id : $source->lead_source_id;
            if(!in_array($source->source_name, $names)) {
                $overrides[$sourceId] = $source;
            }

            // Skip Sources
            if($source->deleted) {
                unset($overrides[$sourceId]);
            }
            $names[] = $source->source_name;
        }

        // Collect Overrides
        return collect($overrides);
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

        // Source Override Exists?
        if(!empty($source->lead_source_id) && !empty($source->user_id)) {
            return $this->update($params);
        }
        // Source Lead Source/User ID Exists
        elseif(!empty($source->lead_source_id) && empty($source->user_id)) {
            // Create Source
            $params['parent_id'] = $source->lead_source_id;
        }

        // Create Source
        return $this->create($params);
    }

    /**
     * Find Lead Source
     * 
     * @param array $params
     * @return LeadSource|null
     */
    public function find($params): ?LeadSource {
        // Find Defaults
        $default = LeadSource::where('source_name', $params['source_name'])
                             ->where('user_id', 0)->first();

        // Find on Dealer
        $source = LeadSource::where('source_name', $params['source_name'])
                            ->where('user_id', $params['user_id'])->first();

        // If Source Exists on Dealer, Return That
        if(!empty($source->lead_source_id)) {
            return $source;
        }

        // Default Exists?
        if(!empty($default->lead_source_id)) {
            // Source Doesn't Exist on Lead, Return Default
            return $default;
        }

        // Default Doesn't Exist, Return Source on Lead
        return $source;
    }
}
