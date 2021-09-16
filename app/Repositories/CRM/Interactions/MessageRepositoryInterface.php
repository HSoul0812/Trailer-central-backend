<?php

namespace App\Repositories\CRM\Interactions;

use App\Repositories\Repository;

/**
 * Interface IntegrationRepositoryInterface
 * @package App\Repositories\Integration
 */
interface MessageRepositoryInterface extends Repository
{
    public function search(array $params);
}
