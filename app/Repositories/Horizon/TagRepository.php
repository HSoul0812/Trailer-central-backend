<?php

namespace App\Repositories\Horizon;

use Laravel\Horizon\Repositories\RedisTagRepository;

class TagRepository extends RedisTagRepository implements TagRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function detach(string $tag, $jobId): int
    {
        return $this->connection()->zrem($tag, $jobId);
    }
}
