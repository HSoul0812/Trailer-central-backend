<?php

namespace App\Nova\Actions\Dealer\Subscriptions\CDK;

use App\Models\User\User;
use App\Services\User\DealerOptionsServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;

class ActivateCdk extends Action
{
    use InteractsWithQueue, Queueable;

    public $showOnTableRow = true;

    public $confirmButtonText = 'Activate';

    public $confirmText = 'Are you sure you want to activate CDK?';

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
            $result = $this->dealerOptionsService->activateCdk($model->dealer_id, $fields->source_id);

            if (!$result) {
                throw new \InvalidArgumentException('CDK activation error', 500);
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
        return [
            Text::make('Source ID', 'source_id')->rules('required'),
        ];
    }
}
