<?php

namespace App\Nova\Actions\Inventory;

use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class UnblockToCollector extends Action
{

    /**
     * Show Title
     * @var string
     */
    public $name = 'Unblock To Collector';

    /**
     * @var bool
     */
    public $showOnTableRow = true;

    /**
     * @var string
     */
    public $confirmButtonText = 'Unblock';

    /**
     * @var string
     */
    public $confirmText = 'Are you sure you want to unblock this inventory?';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models) {
        foreach ($models as $model) {
            $model->changed_fields_in_dashboard = null;
            $model->save();
        }
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields() {
        return [
        ];
    }
}
