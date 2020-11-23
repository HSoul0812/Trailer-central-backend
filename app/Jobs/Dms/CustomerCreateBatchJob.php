<?php


namespace App\Jobs\Dms;


use App\Jobs\Job;
use App\Services\Dms\CustomerCreateBatchService;

class CustomerCreateBatchJob extends Job
{
    /**
     * @var array Customer data in 2D array
     */
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function handle(CustomerCreateBatchService $service)
    {
        return $service->run($this->data);
    }
}
