<?php

namespace App\Services\CRM\Email;

use App\Models\CRM\Email\Blast;

/**
 * Interface BlastServiceInterface
 * @package App\Services\CRM\Email
 */
interface BlastServiceInterface
{
    /**
     * @param array $params
     * @return Blast
     */
    public function create(array $params): Blast;

    /**
     * @param array $params
     * @return Blast
     */
    public function update(array $params): Blast;

    /**
     * @param array $params
     * @return bool
     */
    public function delete(array $params): ?bool;
}
