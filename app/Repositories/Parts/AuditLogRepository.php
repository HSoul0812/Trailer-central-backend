<?php


namespace App\Repositories\Parts;


use App\Exceptions\NotImplementedException;
use App\Models\Parts\AuditLog;
use App\Repositories\RepositoryAbstract;
use App\Utilities\JsonApi\WithRequestQueryable;

class AuditLogRepository extends RepositoryAbstract implements AuditLogRepositoryInterface
{
    use WithRequestQueryable;

    public function getAll($params)
    {
        return $this->query()->get();
    }

    protected function baseQuery()
    {
        return AuditLog::query();
    }

}
