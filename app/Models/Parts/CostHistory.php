<?php

namespace App\Models\Parts;


use App\Utilities\JsonApi\Filterable;
use Illuminate\Database\Eloquent\Model;
use App\Models\CRM\Dms\Quickbooks\Expense;

/**
 * Class CostHistory
 * @package App\Models\Parts
 * @property int $id
 * @property float $old_cost
 * @property float $new_cost
 * @property string $created_at
 * @property string $updated_at
 * @property Part $part
 * @property Expense $expense
 */
class CostHistory extends Model implements Filterable
{
    protected $table = 'parts_cost_history';

    protected $fillable = [
        'part_id',
        'old_cost',
        'new_cost',
        'expense_id'
    ];

    public function part()
    {
        return $this->hasOne(Part::class, 'id', 'part_id');
    }

    public function expense()
    {
        return $this->hasOne(Expense::class, 'id', 'expense_id');
    }

    public function jsonApiFilterableColumns(): ?array
    {
        return ['part_id', 'expense_id'];
    }
}
