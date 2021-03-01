<?php


namespace App\Transformers\Parts;


use App\Models\Parts\AuditLog;
use League\Fractal\TransformerAbstract;

class AuditLogDateTransformer extends TransformerAbstract
{
    public function transform(AuditLog $auditLog)
    {
        return [
            'qty' => $auditLog->balance,
            'sku' => $auditLog->part ? $auditLog->part->sku : 'part-deleted',
            'created_at' => $auditLog->created_at->format('Y-m-d'),
        ];
    }
}
