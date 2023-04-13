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

class IssueCertificateSsl extends Action
{
    use InteractsWithQueue, Queueable;

    public $showOnTableRow = false;

    public $confirmButtonText = 'Issue Certificate';

    public $confirmText = 'Are you sure you want to Issue the Certificate SSL for this website?';

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
            $result = $this->websiteService->certificateDomainSsl($model->id);

            if (!$result) {
                throw new \InvalidArgumentException('An error occurred issuing certified ssl for this website', 500);
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
