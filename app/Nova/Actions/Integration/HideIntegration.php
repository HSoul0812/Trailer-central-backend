<?php

namespace App\Nova\Actions\Integration;

use App\Models\Integration\HiddenIntegration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class HideIntegration extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * Show Title
     * @var string
     */
    public $name = 'Hide Integration';

    /**
     * @var bool
     */
    public $showOnTableRow = true;

    /**
     * @var string
     */
    public $confirmButtonText = 'Hide';

    /**
     * @var string
     */
    public $confirmText = 'Are you sure you want to hide this integration?';

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

            $integration->is_hidden = 1;
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
