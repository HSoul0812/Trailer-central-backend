<?php

namespace App\Nova\Actions\Dealer\Subscriptions\ECommerce;

use App\Models\User\User;
use App\Services\User\DealerOptionsServiceInterface;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class ActivateECommerce extends Action
{

    public $showOnTableRow = true;

    public $confirmButtonText = 'Activate';

    public $confirmText = 'Are you sure you want to activate E-Commerce?';

    /**
     * @var DealerOptionsServiceInterface
     */
    private $dealerOptionsService;

    public function __construct(DealerOptionsServiceInterface $dealerOptionsService)
    {
        $this->dealerOptionsService = $dealerOptionsService;
    }

    /**
     * Perform the action on the given models.
     *
     * @param \Laravel\Nova\Fields\ActionFields $fields
     * @param \Illuminate\Support\Collection $models
     */
    public function handle(ActionFields $fields, Collection $models): void
    {
        /** @var User $model */
        foreach ($models as $model) {
            $this->dealerOptionsService->activateECommerce($model->dealer_id);
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
