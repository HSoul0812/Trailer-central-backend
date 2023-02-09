<?php

namespace App\Jobs\DealerExports;

use App\Jobs\Job;
use App\Models\User\User;
use Exception;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Class CsvExportJob
 *
 * Job wrapper for CsvExporterService
 *
 * @package App\Jobs\DealerExports
 */
class DealerDataExportJob extends Job
{
    use Dispatchable;
    /**
     * @var User
     */
    private $dealer;

    private $actionClass;

    /**
     * @param User $dealer
     * @param string $actionClass
     */
    public function __construct(User $dealer, string $actionClass)
    {
        $this->dealer = $dealer;
        $this->actionClass = $actionClass;
    }

    /**
     * @return bool
     *
     * @throws Exception
     */
    public function handle(): bool
    {
        $action = new $this->actionClass($this->dealer);

        $action->execute();

        return true;
    }
}
