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
            'total_cost' => round($auditLog->part->dealer_cost * $auditLog->balance, 2),
            'total_retail' => round($auditLog->part->price * $auditLog->balance, 2),
            'vendor_name' => $auditLog->part->vendor ? $auditLog->part->vendor->name : null,
            'part_title' => $auditLog->part->title
        ];
    }
}
