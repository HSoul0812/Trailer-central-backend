<?php

declare(strict_types=1);

namespace App\Repositories\Integration\CVR;

use App\Models\Integration\CVR\CvrFile;
use App\Repositories\Common\MonitoredJobRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Describe the API for the repository of CVR files jobs
 */
interface CvrFileRepositoryInterface extends MonitoredJobRepositoryInterface
{
    /**
     * Find a CVR file job by token
     *
     * @param string $token
     * @return CvrFile
     * @throws ModelNotFoundException
     */
    public function findByToken(string $token): CvrFile;

    /**
     * Create a new CVR file
     *
     * @param array $params array of values for the new row
     * @return CvrFile
     */
    public function create(array $params): CvrFile;
}
