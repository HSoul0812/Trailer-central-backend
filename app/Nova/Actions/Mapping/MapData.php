<?php

namespace App\Nova\Actions\Mapping;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;
use App\Models\Feed\Mapping\Incoming\DealerIncomingMapping;

class MapData extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $dealerIncomingData = $models->first();
        
        $status = DealerIncomingMapping::create([
           'dealer_id' => $dealerIncomingData->dealer_id,
           'map_from' => $dealerIncomingData->data,
           'map_to' => $fields->map_to,
           'type' => $dealerIncomingData->type
        ]);
        
        if ($status) {
            Action::message('Mapping added!');
            $dealerIncomingData->delete();
            return Action::push('/resources/dealer-incoming-pending-mappings');
        }
        
        return Action::danger('There was an error adding the mapping.');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Text::make('Map To'),
        ];
    }
    
    public function onlyOnTableRow($value = false)
    {
        return true;
    }
}
