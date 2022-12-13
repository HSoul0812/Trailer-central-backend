<?php

namespace App\Nova\Actions\Mapping;

use App\Models\Inventory\Category;
use App\Models\Inventory\EntityType;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryMfg;
use App\Models\Inventory\Manufacturers\Brand;
use App\Models\Inventory\Status;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;
use App\Models\Feed\Mapping\Incoming\DealerIncomingMapping;
use App\Models\Feed\Mapping\Incoming\DealerIncomingPendingMapping;

use Laravel\Nova\Fields\Select;
use Epartment\NovaDependencyContainer\HasDependencies;
use Epartment\NovaDependencyContainer\NovaDependencyContainer;

class MapData extends Action
{
    use InteractsWithQueue, Queueable;

    public $model;

    function __construct($model = null)
    {
        $this->model = $model;

        if(!is_null($resourceId = request('resourceId'))){
            $this->model = DealerIncomingPendingMapping::find($resourceId);
        }
    }

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

        $mapping = DealerIncomingMapping::create([
            'dealer_id' => $dealerIncomingData->dealer_id,
            'map_from' => $dealerIncomingData->data,
            'map_to' => $fields->map_to,
            'type' => $dealerIncomingData->type
        ]);

        if ($mapping) {
            Action::message('Mapping added!');
            $dealerIncomingData->delete();
            return Action::push('/resources/dealer-incoming-mappings');
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
        $mapTo = Text::make('Map To', 'map_to', function() {
            return $this->model->type;
        });

        switch ($this->model->type) {
            case DealerIncomingMapping::STATUS:
                $mapTo = Select::make('Map To', 'map_to')
                    ->options(Status::select('id', 'label')->orderBy('label')->get()->pluck('label', 'id'))
                    ->rules('required');
                break;
            case DealerIncomingMapping::MAKE:
                $mapTo = Select::make('Map To', 'map_to')
                    ->options(InventoryMfg::select('label')->orderBy('label')->get()->pluck('label', 'label'))
                    ->rules('required');
                break;
            case DealerIncomingMapping::BRAND:
                $mapTo = Select::make('Map To', 'map_to')
                    ->options(Brand::select('name')->orderBy('name')->get()->pluck('name', 'name'))
                    ->rules('required');
                break;
            case DealerIncomingMapping::CONDITION:
                $mapTo = Select::make('Map To', 'map_to')
                    ->options(Inventory::CONDITION_MAPPING)
                    ->rules('required');
                break;
            case DealerIncomingMapping::CATEGORY:
                $mapTo = Select::make('Map To', 'map_to')
                    ->options(Category::select('legacy_category', 'label')->orderBy('label')->get()->pluck('label', 'legacy_category'))
                    ->rules('required');
                break;
        }

        return [$mapTo];
    }
}
