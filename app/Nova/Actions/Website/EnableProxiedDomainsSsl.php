<?php

namespace App\Nova\Actions\Dealer\Subscriptions\Google;

use App\Models\User\User;
use App\Services\Website\WebsiteService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Text;

class EnableProxiedDomainsSsl extends Action
{
    use InteractsWithQueue, Queueable;

    public $showOnTableRow = true;

    public $confirmButtonText = 'Enable Proxied Domains';

    public $confirmText = 'Are you sure you want to enable proxied domains SSL for this website?';

    /**
     * @var WebsiteService
     */
    private $websiteService;

    public function __construct(WebsiteService $websiteService)
    {
        $this->websiteService = $websiteService;
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
            $result = $this->websiteService->enableProxiedDomainSsl($model->id);

            if (!$result) {
                throw new \InvalidArgumentException('An error occurred enabling proxied domains ssl for this website', 500);
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
