<?php


namespace App\Repositories\Parts;


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

    /**
     * @param $params
     * @return AuditLog
     */
    public function create($params)
    {
        $auditLog = new AuditLog($params);
        $auditLog->save();

        return $auditLog;
    }

    protected function baseQuery()
    {
        return AuditLog::query();
    }

}
