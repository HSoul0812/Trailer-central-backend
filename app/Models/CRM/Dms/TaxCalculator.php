<?php


namespace App\Models\CRM\Dms;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * @property int $dealer_id
 *
 * @method static Builder select($columns = ['*'])
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static Builder whereIn($column, $values, $boolean = 'and', $not = false)
 * @method static Builder find($id, $columns = ['*'])
 */
class TaxCalculator extends Model
{
    protected $table = "dms_tax_calculators";
}
