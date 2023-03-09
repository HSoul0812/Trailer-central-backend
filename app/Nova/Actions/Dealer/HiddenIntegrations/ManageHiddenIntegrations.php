<?php

namespace App\Nova\Actions\Dealer\HiddenIntegrations;

use App\Models\User\User;
use Illuminate\Bus\Queueable;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Actions\Action;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use App\Models\Integration\Integration;
use Illuminate\Queue\InteractsWithQueue;
use App\Services\User\DealerOptionsServiceInterface;

/**
 * class ManageHiddenIntegrations
 *
 * @package App\Nova\Actions\Dealer\HiddenIntegrations
 */
class ManageHiddenIntegrations extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * @var bool
     */
    public $showOnTableRow = true;

    /**
     * @var DealerOptionsServiceInterface
     */
    private $dealerOptionsService;

    /**
     * @var Collection<Integration>
     */
    private $hiddenIntegrations;

    /**
     * @param DealerOptionsServiceInterface $dealerOptionsService
     */
    public function __construct(DealerOptionsServiceInterface $dealerOptionsService)
    {
        $this->dealerOptionsService = $dealerOptionsService;
        $this->hiddenIntegrations = Integration::activeHiddenIntegrations()->pluck('name', 'integration_id');
    }

    /**
     * Perform the action on the given models.
     *
     * @param ActionFields $fields
     * @param Collection $models
     */
    public function handle(ActionFields $fields, Collection $models): void
    {
        /** @var User $model */
        foreach ($models as $model) {
            $result = $this->dealerOptionsService->manageHiddenIntegration(
                $model->dealer_id,
                $fields->integration_id,
                $fields->active
            );

            if (!$result) {
                throw new \InvalidArgumentException('Something went wrong.', 500);
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
            Select::make('Hidden Integration', 'integration_id')
                ->options(
                   $this->hiddenIntegrations
                )
                ->rules('required')
                ->sortable(),
            Select::make('Option', 'active')
                ->options(
                    [
                        1 => 'Activate',
                        0 => 'Deactivate'
                    ]
                )
                ->rules('required')
                ->sortable(),
        ];
    }
}
