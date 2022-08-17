<?php

namespace App\Nova\Actions\Dealer;

use App\Models\User\User;
use App\Services\User\DealerOptionsServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

/**
 * Class DeactivateDealer
 * @package App\Nova\Actions\Dealer
 */
class DeactivateDealer extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * @var bool
     */
    public $showOnTableRow = true;

    /**
     * @var string
     */
    public $confirmButtonText = 'Deactivate';

    /**
     * @var string
     */
    public $confirmText = 'Are you sure you want to deactivate Dealer and Archive his inventory? This action cannot be undone.';

    /**
     * @var DealerOptionsServiceInterface
     */
    private $dealerOptionsService;

    /**
     * @param DealerOptionsServiceInterface $dealerOptionsService
     */
    public function __construct(DealerOptionsServiceInterface $dealerOptionsService) {
        $this->dealerOptionsService = $dealerOptionsService;
    }

    /**
     * Perform the action on the given models.
     *
     * @param ActionFields $fields
     * @param Collection $models
     */
    public function handle(ActionFields $fields, Collection $models): void {
        /** @var User $model */
        foreach ($models as $model) {
            $result = $this->dealerOptionsService->deactivateDealer($model->dealer_id);

            if (!$result) {
                throw new InvalidArgumentException('Error trying deactivate Dealer', 500);
            }
        }
    }

}
