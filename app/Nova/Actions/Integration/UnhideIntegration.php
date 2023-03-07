<?php

namespace App\Nova\Actions\Integration;

use App\Models\Integration\HiddenIntegration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class UnhideIntegration extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * Show Title
     * @var string
     */
    public $name = 'Unhide Integration';

    /**
     * @var bool
     */
    public $showOnTableRow = true;

    /**
     * @var string
     */
    public $confirmButtonText = 'Unhide';

    /**
     * @var string
     */
    public $confirmText = 'Are you sure you want to unhide this integration?';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        foreach ($models as $model) {
            $integration = HiddenIntegration::firstOrNew([
                'integration_id' => $model->integration_id
            ]);

            $integration->is_hidden = 0;
            $integration->save();
        }
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [];
    }
}
