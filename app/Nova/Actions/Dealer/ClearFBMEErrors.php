<?php

namespace App\Nova\Actions\Dealer;

use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use App\Models\CRM\Dealer\DealerFBMOverview;
use App\Models\Marketing\Facebook\Error;
use App\Repositories\Marketing\Facebook\ErrorRepositoryInterface;
use App\Services\Dispatch\Facebook\MarketplaceServiceInterface;

class ClearFBMEErrors extends Action
{

    public $name = "Clear Errors";

    public $showOnTableRow = true;

    public $confirmButtonText = 'Clear Facebook Errors';

    public $confirmText = 'Are you sure you want to clear all Facebook Marketplace Extension errors for this integration?';

    /**
     * @var ErrorRepositoryInterface
     */
    private $fbErrors;

    public function __construct(ErrorRepositoryInterface $fbErrors)
    {
        $this->fbErrors = $fbErrors;
    }

    /**
     * Perform the action on the given models.
     *
     * @param \Laravel\Nova\Fields\ActionFields $fields
     * @param \Illuminate\Support\Collection $models
     * @return array
     */
    public function handle(ActionFields $fields, Collection $models): array
    {
        $nrErrorsCleared = 0;
        /** @var DealerFBMOverview $model */
        foreach ($models as $model) {
            $nrErrorsCleared += $this->fbErrors->dismissAllActiveForIntegration($model->id)->count();
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
