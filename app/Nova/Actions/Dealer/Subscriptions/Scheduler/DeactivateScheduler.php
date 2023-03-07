<?php

namespace App\Nova\Actions\Dealer\Subscriptions\Scheduler;

use App\Models\User\User;
use App\Services\User\DealerOptionsServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class DeactivateScheduler extends Action
{
    use InteractsWithQueue, Queueable;

    public $showOnTableRow = true;

    public $confirmButtonText = 'Deactivate';

    public $confirmText = 'Are you sure you want to deactivate Scheduler?';

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
            $result = $this->dealerOptionsService->deactivateScheduler($model->dealer_id);

            if (!$result) {
                throw new \InvalidArgumentException('Scheduler deactivation error', 500);
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
