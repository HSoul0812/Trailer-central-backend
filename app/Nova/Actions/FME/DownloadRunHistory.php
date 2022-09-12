<?php

namespace App\Nova\Actions\FME;

use App\Services\Dispatch\Facebook\PostingHistoryServiceInterface;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use App\Models\CRM\Dealer\DealerFBMOverview;
use Exception;

class DownloadRunHistory extends Action
{
    /**
     * @var string
     */
    public $name = 'Download Run History for All Integrations';

    /**
     * @var string
     */
    public $confirmButtonText = 'Download';

    /**
     * @var string
     */
    public $confirmText = 'Are you sure you want to generate and download the history for all runs for all dealers?';
    /**
     * @var PostingHistoryServiceInterface
     */
    private $service;

    public function __construct(PostingHistoryServiceInterface $service)
    {
        $this->service = $service;
    }


    /**
     * Perform the action on the given models.
     *
     * @param ActionFields $fields
     * @param Collection<DealerFBMOverview> $models
     * @return array
     */
    public function handle(ActionFields $fields, Collection $models): array
    {
        $fileName = $this->getFileName();

        try {
            $url = $this->service->exportAll($fileName);

            return $url ? self::download($url, $fileName) : self::danger('The URL was not generated correctly.');
        } catch (Exception $exception) {
            // Return it to the client
            return self::danger('There was an error generating the report. ' . $exception->getMessage());
        }
    }

    private function getFileName(): string
    {
        $date = now()->format('Y-m-d_H-i-s');
        return "export_postingHistory_all_{$date}.csv";
    }
}
