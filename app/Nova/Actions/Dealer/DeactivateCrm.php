<?php

namespace App\Nova\Actions\Dealer;

use App\Models\User\User;
use App\Services\User\DealerOptionsService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

/**
 * Class DeactivateCrm
 * @package App\Nova\Actions\Dealer
 */
class DeactivateCrm extends Action
{
    use InteractsWithQueue, Queueable;

    public $showOnTableRow = true;

    public $confirmButtonText = 'Deactivate';

    public $confirmText = 'Are you sure you want to deactivate CRM?';

    /**
     * @var DealerOptionsService
     */
    private $dealerOptionsService;

    public function __construct(DealerOptionsService $dealerOptionsService)
    {
        $this->dealerOptionsService = $dealerOptionsService;
    }

    /**
     * Perform the action on the given models.
     *
     * @param \Laravel\Nova\Fields\ActionFields $fields
     * @param \Illuminate\Support\Collection $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        /** @var User $model */
        foreach ($models as $model) {
            $this->dealerOptionsService->deactivateCrm($model->dealer_id);
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
