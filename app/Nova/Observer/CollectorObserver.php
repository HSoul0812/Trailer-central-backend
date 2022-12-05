<?php

namespace App\Nova\Observer;

use Illuminate\Support\Facades\Auth;

use App\Models\Integration\Collector\Collector;
use App\Models\Integration\Collector\CollectorChangeReport;

/**
 * Class CollectorObserver
 * @package App\Nova\Observer
 */
class CollectorObserver
{
    /**
     * @param Collector $collector
     */
    public function updating(Collector $collector): void
    {
        if ($collector->isDirty()) {
            foreach ($collector->getOriginal() as $key => $value) {
                if ($collector->isDirty($key) && $collector->getOriginal($key) != $collector->{$key}) {
                    $changeReport = new CollectorChangeReport();
                    $changeReport->collector_id = $collector->id;
                    $changeReport->user_id = Auth::user()->id;
                    $changeReport->field = $key;

                    if ($key == 'overridable_fields') {
                        $changeReport->changed_from = $this->overridableFieldsEnabled(json_decode($collector->getOriginal($key), true));
                        $changeReport->changed_to = $this->overridableFieldsEnabled($collector->{$key});
                    } else {
                        $changeReport->changed_to = $collector->{$key};
                        $changeReport->changed_from = $collector->getOriginal($key);
                    }

                    $changeReport->save();
                }
            }
        }
    }

    public function overridableFieldsEnabled($overridableFields): string
    {
        $overridable_fields = array_keys(array_filter($overridableFields, function($field){
            return $field;
        }));

        return implode(", ", $overridable_fields);
    }
}
