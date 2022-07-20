<?php

namespace App\Nova\Actions\Dealer;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use App\Models\CRM\Dealer\DealerFBMOverview;

class ClearFBMEErrors extends Action
{
    use InteractsWithQueue, Queueable;

    public $name = "Clear Errors";

    public $showOnTableRow = true;

    public $confirmButtonText = 'Clear Facebook Errors';

    public $confirmText = 'Are you sure you want to clear all Facebook Marketplace Extension errors?';

    /**
     * Perform the action on the given models.
     *
     * @param \Laravel\Nova\Fields\ActionFields $fields
     * @param \Illuminate\Support\Collection $models
     */
    public function handle(ActionFields $fields, Collection $models): void
    {
        /** @var DealerFBMOverview $model */
        foreach ($models as $model) {
            if (!$model->clearErrors()) {
                throw new \InvalidArgumentException('FBME error', 500);
            }
        }
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(): array
    {
        return [];
    }
}
