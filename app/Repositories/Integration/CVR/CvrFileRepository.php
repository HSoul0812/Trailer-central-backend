<?php

declare(strict_types=1);

namespace App\Repositories\Integration\CVR;

use App\Models\Integration\CVR\CvrFile;
use App\Repositories\Common\MonitoredJobRepository;

/**
 * Implementation for CVR file repository
 */
class CvrFileRepository extends MonitoredJobRepository implements CvrFileRepositoryInterface
{
    /**
     * @param string $token
     * @return CvrFile|null
     */
    public function findByToken(string $token): ?CvrFile
    {
        return CvrFile::where('token', $token)->get()->first();
    }

    /**
     * @param array $params
     *
     * @return CvrFile
     */
    public function create(array $params): CvrFile
    {
        return CvrFile::create($params);
    }
}
