<?php

namespace App\Repositories\Parts;

use App\Models\Parts\AuditLog;
use App\Repositories\RepositoryAbstract;
use App\Utilities\JsonApi\WithRequestQueryable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use App\Transformers\Parts\AuditLogDateTransformer;
use Illuminate\Support\Facades\Storage;

class AuditLogRepository extends RepositoryAbstract implements AuditLogRepositoryInterface
{
    use WithRequestQueryable;
    
    /**     
     * @var App\Models\Parts\AuditLog 
     */
    protected $model;
    
    /**
     * @var App\Transformers\Parts\AuditLogDateTransformer
     */
    protected $auditLogDateTransformer;
    
    public function __construct(AuditLog $auditLog)
    {
        $this->model = $auditLog;
        $this->auditLogDateTransformer = new AuditLogDateTransformer();
    }

    public function getAll($params)
    {
        return $this->query()->get();
    }
    
    public function getByDate(Carbon $date, int $dealerId) : Collection
    {
        return $this->model
                    ->join('parts_v1', 'parts_v1.id', '=', 'parts_audit_log.part_id')
                    ->whereBetween('parts_audit_log.created_at', [$date->format('Y-m-d').' 00:00:00', $date->format('Y-m-d').' 23:59:59'])
                    ->where('parts_v1.dealer_id', $dealerId)
                    ->get();
    }
    
    public function getByYear(int $year, int $dealerId) : Builder
    {
        return $this->model
                    ->select("parts_audit_log.*", "parts_v1.id", "parts_v1.dealer_id", "parts_v1.dealer_cost", "parts_v1.price", "parts_v1.vendor_id")
                    ->join('parts_v1', 'parts_v1.id', '=', 'parts_audit_log.part_id')
                    ->whereBetween('parts_audit_log.created_at', ["$year-01-01 00:00:00", "$year-12-31 23:59:59"])
                    ->where('parts_audit_log.balance', '>', 0)
                    ->where('parts_v1.dealer_id', $dealerId)
                    ->orderBy('parts_audit_log.created_at', 'DESC');
    }
    
    public function getByYearCsv(int $year, int $dealerId) : array
    {
        $fileName = '/storage/'.uniqid().".csv";
        $fileExport = "/var/www/html/public/temp$fileName";

        $directoryPath = dirname($fileExport);
        if(!file_exists($directoryPath)){
            mkdir($directoryPath, 0777, true);
        }

        $fp = fopen($fileExport, 'w+');
        $this->getByYear($year, $dealerId)->chunk(100, function($auditLogs) use (&$fp) {
            foreach($auditLogs as $auditLog) {
                fputcsv($fp, array_values($this->auditLogDateTransformer->transform($auditLog)));
            }
        });
        fclose($fp);
        $result = Storage::disk('s3')->put($fileName, file_get_contents($fileExport));
        if ($result){
            return [
                'export_file' => 'https://'.env('AWS_BUCKET').'.s3.amazonaws.com'.$fileName
            ];
        }
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
