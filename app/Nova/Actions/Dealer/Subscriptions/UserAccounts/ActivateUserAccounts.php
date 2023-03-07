<?php

namespace App\Nova\Actions;

use App\Models\User\User;
use App\Services\User\DealerOptionsServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class ActivateUserAccounts extends Action
{
    use InteractsWithQueue, Queueable;

    public $showOnTableRow = true;

    public $confirmButtonText = 'Activate UserAccounts';

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
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        /** @var User $model */
        foreach ($models as $model) {
            $result = $this->dealerOptionsService->activateUserAccounts($model->dealer_id);

            if (!$result) {
                throw new \InvalidArgumentException('User accounts activation error', 500);
            }
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
