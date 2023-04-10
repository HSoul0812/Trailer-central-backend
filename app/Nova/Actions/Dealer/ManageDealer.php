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
use Laravel\Nova\Fields\Select;

/**
 * Class ManageDealer
 * @package App\Nova\Actions\Dealer
 */
class ManageDealer extends Action
{
    use InteractsWithQueue;
    use Queueable;

    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Manage Dealer Active State';

    /**
     * @var bool
     */
    public $showOnTableRow = true;

    /**
     * @var string
     */
    public $confirmText = 'Are you sure you want to perform this action?.';

    /**
     * @var DealerOptionsServiceInterface
     */
    private $dealerOptionsService;

    /**
     * @param DealerOptionsServiceInterface $dealerOptionsService
     */
    public function __construct(DealerOptionsServiceInterface $dealerOptionsService)
    {
        $this->dealerOptionsService = $dealerOptionsService;
    }

    /**
     * Perform the action on the given models.
     *
     * @param ActionFields $fields
     * @param Collection $models
     * @return void
     * @throws \Exception
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        /** @var User $model */
        foreach ($models as $model) {
            $message = "Dealer $model->dealer_id is already ";

            if (!$model->deleted && $fields->active) {
                return Action::danger($message . ' activated.');
            }

            if ($model->deleted && !$fields->active) {
                return Action::danger($message . ' deactivated.');
            }

            try {
                $result = $this->dealerOptionsService->toggleDealerActiveStatus($model->dealer_id, $fields->active);

                if (!$result) {
                    throw new InvalidArgumentException('Error trying managing Dealer', 500);
                }

            } catch (\InvalidArgumentException|\Exception $e) {
                Action::message($e->getMessage());
                throw new \Exception($e->getMessage(), 500);
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
            Select::make('State', 'active')
                ->options([
                    1 => 'Activate',
                    0 => 'Deactivate'
                ])
                ->rules('required')
                ->help('<ul>
                            <li>Activating dealer will unarchive his inventory based on previous deactivation date.</li>
                            <li>Deactivating dealer will archive his inventory.</li>
                        </ul>'),
        ];
    }
}
