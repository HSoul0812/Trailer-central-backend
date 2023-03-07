<?php

namespace App\Repositories\Horizon;

use Laravel\Horizon\Contracts\TagRepository;

interface TagRepositoryInterface extends TagRepository
{
    /**
     * Detaches a job from a batch
     *
     * @param  string  $tag
     * @param  string|int  $jobId
     * @return int
     */
    public function detach(string $tag, $jobId): int;
}
