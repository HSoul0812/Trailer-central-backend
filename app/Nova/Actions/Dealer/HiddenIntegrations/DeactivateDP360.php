<?php

namespace App\Nova\Actions\Dealer\HiddenIntegrations;

use App\Models\User\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use App\Services\User\DealerOptionsServiceInterface;

class DeactivateDP360 extends Action
{
    use InteractsWithQueue, Queueable;

    public $showOnTableRow = true;

    public $confirmButtonText = 'Deactivate';

    public $confirmText = 'Are you sure you want to deactivate DP360?';

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
            $result = $this->dealerOptionsService->deactivateDP360($model->dealer_id);

            if (!$result) {
                throw new \InvalidArgumentException('DP360 deactivation error', 500);
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
