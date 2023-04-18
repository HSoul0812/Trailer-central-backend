<?php

declare(strict_types=1);

namespace App\Models\User\Integration;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Integration\Integration;

/**
 * @property int $integration_dealer_id
 * @property int $integration_id
 * @property int $dealer_id
 * @property \DateTimeInterface $created_at
 * @property \DateTimeInterface $updated_at
 * @property \DateTimeInterface $last_run_at
 * @property \DateTimeInterface $msg_date
 * @property int $active 0 or 1
 * @property string $settings originally encoded as PHP serialized string
 * @property string $filters originally encoded as PHP serialized string
 * @property string $location_ids  Comma separated values
 * @property string $msg_title
 * @property string $msg_body
 * @property int $include_pending_sale 0 or 1
 *
 * @property Integration $integration
 *
 * @method static \Illuminate\Database\Query\Builder select($columns = ['*'])
 * @method static \Illuminate\Database\Query\Builder where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static \Illuminate\Database\Query\Builder whereIn($column, $values, $boolean = 'and', $not = false)
 * @method static DealerIntegration findOrFail($id, array $columns = ['*'])
 * @method static DealerIntegration|Collection|static[]|static|null find($id, $columns = ['*'])
 */
class DealerIntegration extends Model
{
    public const STATUS_ACTIVE = 1;

    protected $table = 'integration_dealer';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'integration_dealer_id';

    protected $fillable = [
        'integration_id',
        'dealer_id',
        'last_run_at',
        'msg_date',
        'active',
        'settings',
        'filters',
        'location_ids',
        'msg_title',
        'msg_body',
        'include_pending_sale'
    ];

    protected $casts = [
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'last_run_at' => 'datetime',
        'msg_date'    => 'datetime'
    ];

    public function integration(): BelongsTo
    {
        return $this->belongsTo(Integration::class, 'integration_id', 'integration_id');
    }

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dealer_id', 'dealer_id');
    }

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
     * To avoid mutations and break something
     *
     * @return \Illuminate\Support\Collection
     */
    public function decodeSettingsWithValues(): \Illuminate\Support\Collection
    {
        $settingValues = $this->decodeSettings();

        $settingValueMapper = static function (array $setting) use ($settingValues): array {
            return $setting + ['value' => isset($setting['name']) ? $settingValues->get($setting['name']) : ''];
        };

        return $this->integration->decodeSettings()->keyBy('name')->map($settingValueMapper);
    }

    /**
     * To avoid mutations and break something
     *
     * @return \Illuminate\Support\Collection
     */
    public function decodeLocationIds(): \Illuminate\Support\Collection
    {
        return collect(array_filter(explode(',', trim($this->location_ids ?? ''))));
    }
}
