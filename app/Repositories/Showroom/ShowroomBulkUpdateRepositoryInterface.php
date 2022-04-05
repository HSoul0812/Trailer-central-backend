<?php

namespace App\Repositories\Showroom;

use Dingo\Api\Http\Request;
use App\Repositories\Repository;

/**
 * Interface ShowroomBulkUpdateRepositoryInterface
 * @package App\Repositories\Manufacturer
 */
interface ShowroomBulkUpdateRepositoryInterface extends Repository
{
    public function bulkUpdate(string $manufacturer, array $params);
    public function bulkUpdateYear(Request $request);
    public function bulkUpdateVisibility(Request $request);
}
