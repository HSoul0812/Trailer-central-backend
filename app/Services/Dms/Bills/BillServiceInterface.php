<?php
namespace App\Services\Dms\Bills;

use App\Models\CRM\Dms\Quickbooks\Bill;

interface BillServiceInterface
{
    public function create(array $params): Bill;
    public function update(array $params): Bill;
    public function get(array $params): Bill;
    public function getAll(array $params, bool $paginated);
    public function delete(array $params);
}