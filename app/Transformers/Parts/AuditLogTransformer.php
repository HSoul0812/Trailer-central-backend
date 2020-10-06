<?php


namespace App\Transformers\Parts;


use App\Models\Parts\AuditLog;
use League\Fractal\TransformerAbstract;

class AuditLogTransformer extends TransformerAbstract
{

    protected $availableIncludes = [
        'part',
        'bin',
    ];

    public function transform(AuditLog $auditLog)
    {
        return [
            'qty' => $auditLog->qty,
            'balance' => $auditLog->balance,
            'description' => $auditLog->description,
            'created_at' => $auditLog->created_at,
        ];
    }

    public function includePart(AuditLog $auditLog)
    {
        return $this->item($auditLog->part, new PartsTransformer());
    }

    public function includeBin(AuditLog $auditLog)
    {
        return $this->item($auditLog->bin, new BinTransformer());
    }

}
