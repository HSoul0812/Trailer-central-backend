<?php

namespace App\Nova\Actions\Dealer\Subscriptions\DealerClassifieds;

use App\Models\User\User;
use Illuminate\Bus\Queueable;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Actions\Action;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Illuminate\Queue\InteractsWithQueue;
use App\Services\User\DealerOptionsServiceInterface;
use Epartment\NovaDependencyContainer\HasDependencies;
use Epartment\NovaDependencyContainer\NovaDependencyContainer;

/**
 * class ManageDealerSubscriptions
 *
 * @package App\Nova\Actions\Dealer\Subscriptions\DealerClassifieds
 */
class ManageDealerSubscriptions extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Manage Subscriptions';

    /**
     * @var bool
     */
    public $showOnTableRow = true;

    /**
     * @var DealerOptionsServiceInterface
     */
    private $dealerOptionsService;

    /**
     * @var array
     */
    private $subscriptions = [
        'cdk' => 'CDK Leads', //special
        'crm' => 'CRM', // special
        'is_dms_active' => 'DMS',
        'clsf_active' => 'DealerClassifieds',
        'ecommerce' => 'E-Commerce', // special
        'google_feed_active' => 'GoogleFeed',
        'marketing' => 'Marketing', // special
        'mobile' => 'MobileSite', // special
        'parts' => 'Parts', // special
        'is_quote_manager_active' => 'QuoteManager',
        'is_scheduler_active' => 'Scheduler',
        'user_accounts' => 'UserAccounts', // special
    ];

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
     * @throws \Exception
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        /** @var User $model */
        foreach ($models as $model) {
            try {
                $result = $this->dealerOptionsService->manageDealerSubscription(
                    $model->dealer_id,
                    $fields
                );

                if (!$result) {
                    throw new \InvalidArgumentException('Something went wrong', 500);
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
            Select::make('Subscription', 'subscription')
                ->options(
                    $this->subscriptions
                )
                ->rules('required')
                ->sortable(),

            // CDK Leads
            NovaDependencyContainer::make([
                Text::make('Source ID', 'source_id')->help(
                    'The Source ID will be ignored when deactivating the subscription, can be blank.'
                ),
            ])->dependsOn('subscription', 'cdk'),
            // CDK Leads

            Select::make('Status', 'active')
                ->options(
                    [
                        1 => 'Active',
                        0 => 'Inactive'
                    ]
                )
                ->rules('required')
                ->sortable(),
        ];
    }
}
