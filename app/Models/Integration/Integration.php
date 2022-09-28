<?php

namespace App\Models\Integration;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class Integration
 * @package App\Models\Integration
 *
 * * @property bool $isHidden
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
class Integration extends Model
{
    public const STATUS_ACTIVE = 1;

    // Define Table Name Constant
    const TABLE_NAME = 'integration';

    /**
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * @var string
     */
    protected $primaryKey = 'integration_id';

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
        'code',
        'module_name',
        'module_status',
        'name',
        'description',
        'domain',
        'create_account_url',
        'active',
        'filters',
        'frequency',
        'last_run_at',
        'settings',
        'include_sold',
        'send_email',
        'uses_staging',
        'show_for_integrated'
    ];

    /**
     * To avoid mutations and break something
     *
     * @return \Illuminate\Support\Collection
     */
    public function decodeSettings(): \Illuminate\Support\Collection
    {
        return collect(!empty($this->settings) ? (@unserialize($this->settings) ? unserialize($this->settings, ['allowed_classes' => false]) : []) : []);
    }

    /**
     * If is a hidden integration
     *
     * @return bool
     */
    public function getIsHiddenAttribute(): bool
    {
        $integration = HiddenIntegration::where('integration_id', $this->integration_id)->first();

        if ($integration) {
            return $integration->is_hidden;
        } else {
            return false;
        }
    }

    /**
     * Get Dealers
     */
    public function dealers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'integration_dealer', 'integration_id', 'dealer_id')->withPivot(['active']);
    }
}
