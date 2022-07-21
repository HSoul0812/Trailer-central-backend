<?php

namespace App\Nova\Actions\Dealer;

use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use App\Models\CRM\Dealer\DealerFBMOverview;
use App\Repositories\Marketing\Facebook\ErrorRepository;

class ClearFBMEErrors extends Action
{

    public $name = "Clear Errors";

    public $showOnTableRow = true;

    public $confirmButtonText = 'Clear Facebook Errors';

    public $confirmText = 'Are you sure you want to clear all Facebook Marketplace Extension errors for this integration?';


    /**
     * Perform the action on the given models.
     *
     * @param \Laravel\Nova\Fields\ActionFields $fields
     * @param \Illuminate\Support\Collection $models
     * @return array
     */
    public function handle(ActionFields $fields, Collection $models): array
    {
        $errors = new ErrorRepository();
        $nrErrorsCleared = 0;
        /** @var DealerFBMOverview $model */
        foreach ($models as $model) {
            $nrErrorsCleared += ($errors->dismissAllActiveForIntegration($model->marketplace_id))->count();
        }

        return self::message(($nrErrorsCleared > 0) ? "Errors cleared!" : "There are no errors to clear!");
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
