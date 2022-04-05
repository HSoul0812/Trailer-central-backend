<?php

namespace App\Jobs\Showroom;

use App\Jobs\Job;
use App\Services\Showroom\ShowroomBulkUpdateYearService;
use Illuminate\Support\Facades\Log;

class ShowroomBulkUpdateYear extends Job {

    //public $timeout = 0;
    public $tries = 2;

    /**
     * @var array
     */
    protected $params;

    /**
     * @var ShowroomBulkUpdateYearService
     */
    protected $bulkUpdateYearService;

    /**
     * Create a new job instance.
     *
     * @param array $params
     * @throws \Exception
     */
    public function __construct($params)
    {
        $this->params = $params;
        $this->bulkUpdateYearService = new ShowroomBulkUpdateYearService($params);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('Starting Bulk Manufacturer Update Year');
        try {
            $this->bulkUpdateYearService->update();
        } catch (\Exception $ex) {
            Log::info($ex->getMessage());
        }
    }
}
