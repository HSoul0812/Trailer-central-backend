<?php


namespace App\Models\Parts;


use App\Utilities\JsonApi\Filterable;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AuditLog
 * @package App\Models\Parts
 * @property int $id
 * @property int $qty
 * @property int $balance
 * @property string $description
 * @property Part $part
 * @property Bin $bin
 */
class AuditLog extends Model implements Filterable
{
    protected $table = 'parts_audit_log';

    public function part()
    {
        return $this->hasOne(Part::class, 'id', 'part_id');
    }

    public function bin()
    {
        return $this->hasOne(Bin::class, 'id', 'bin_id');
    }

    public function jsonApiFilterableColumns(): ?array
    {
        return ['dealer_id', 'part_id', 'created_at'];
    }
}
