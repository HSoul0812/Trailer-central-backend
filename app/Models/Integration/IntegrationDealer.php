<?php

namespace App\Models\Integration;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Integration
 * @package App\Models\Integration
 *
 * @property int $integration_id
 * @property string $name
 * @property string $description
 * @property string $domain
 * @property string $code
 * @property string $create_account_url
 * @property string $settings originally encoded as PHP serialized string
 * @property string $filters originally encoded as PHP serialized string
 */
class IntegrationDealer extends Model
{
    // Define Table Name Constant
    const TABLE_NAME = 'integration_dealer';

    /**
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * @var string
     */
    protected $primaryKey = 'integration_dealer_id';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'integration_id',
        'dealer_id',
        'last_run_at',
        'active',
        'settings',
        'filters',
        'location_ids',
        'msg_title',
        'msg_body',
        'msg_date',
        'include_pending_sale'
    ];

    /**
     * Returns integration
     *
     * @return BelongsTo
     */
    public function integration() : BelongsTo
    {
        return $this->belongsTo(Integration::class, 'integration_id', 'integration_id')->withPivot('active');
    }

    /**
     * Returns dealer
     *
     * @return BelongsTo
     */
    public function dealer() : BelongsTo
    {
        return $this->belongsTo(User::class, 'dealer_id', 'dealer_id')->withPivot('active');
    }
}
