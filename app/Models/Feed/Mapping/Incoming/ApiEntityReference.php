<?php

namespace App\Models\Feed\Mapping\Incoming;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * @property int $api_entity_reference_id
 * @property int|null $entity_id
 * @property string|null $reference_id
 * @property string $entity_type
 * @property string $api_key
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and')
 */
class ApiEntityReference extends Model {

    use TableAware;

    protected $table = 'api_entity_reference';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'api_entity_reference_id';

    public $timestamps = false;

    const LOADTRAIL_API_KEY = 'lt';
    const PJ_API_KEY = 'pj';
    const UTC_API_KEY = 'utc';
    const LGS_API_KEY = 'lgs';
    const NOVAE_API_KEY = 'novae';
    const LAMAR_API_KEY = 'lamar';
    const NORSTAR_API_KEY = 'norstar';
    const BIGTEX_API_KEY = 'bigtex';

    public const TYPE_LOCATION = 'dealer_location';

    protected $fillable = [
        'entity_id',
        'reference_id',
        'entity_type',
        'api_key'
    ];
}
