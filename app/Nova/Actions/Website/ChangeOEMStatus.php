<?php

namespace App\Nova\Actions\Website;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class ChangeOEMStatus extends Action
{
    use InteractsWithQueue, Queueable;

    public $showOnTableRow = true;

    public $confirmText = 'Are you sure you want to change the OEM status?';

    public $confirmButtonText = 'Yes';

    /**
     * Perform the action on the given models.
     *
     * @param ActionFields $fields
     * @param Collection $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $models->each(function ($model) {
            $model->is_oem = !$model->is_oem;
            $model->save();
        });
        return Action::message('OEM status updated');
    }

    public function name(): string
    {
        return 'Change OEM Status';
    }
}
