<?php

declare(strict_types=1);

namespace App\Repositories\Integration\CVR;

use App\Models\Integration\CVR\CvrFile;
use App\Repositories\Common\MonitoredJobRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Implementation for CVR file repository
 */
class CvrFileRepository extends MonitoredJobRepository implements CvrFileRepositoryInterface
{
    /**
     * @param string $token
     * @return CvrFile
     * @throws ModelNotFoundException
     */
    public function findByToken(string $token): CvrFile
    {
        return CvrFile::findOrFail($token);
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
