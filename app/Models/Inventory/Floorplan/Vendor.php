<?php

declare(strict_types=1);

namespace App\Models\Inventory\Floorplan;

use App\Models\Parts\Vendor as PartsVendor;
use Illuminate\Database\Query\Builder;

/**
 * @property int $id
 * @property string $name
 *
 * @method static Builder select($columns = ['*'])
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and')
 */
class Vendor extends PartsVendor {

}
