<?php

namespace App\Nova\Actions\Dealer;

use App\Models\User\User;
use App\Services\User\DealerOptionsServiceInterface;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class DeactivateECommerce extends Action
{

    public $showOnTableRow = true;

    public $confirmButtonText = 'Deactivate';

    public $confirmText = 'Are you sure you want to deactivate E-Commerce?';

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
            $this->dealerOptionsService->deactivateECommerce($model->dealer_id);
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