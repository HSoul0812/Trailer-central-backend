<?php

namespace App\Models\User\Integration;

use App\Models\User\Interfaces\PermissionsInterface;
use App\Traits\Models\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class InteractionIntegration
 * @package App\Models\CRM\Interactions
 *
 * @property integer $id
 * @property string $name
 * @property \DateTimeInterface $created_at
 * @property \DateTimeInterface $updated_at
 */
class Integration extends Model implements Authenticatable, PermissionsInterface
{
    use HasPermissions;

    protected $table = 'interaction_integration';

    protected $fillable = [
        'name',
    ];

    /**
     * {@inheritDoc}
     */
    public function getAuthIdentifierName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthIdentifier(): int
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthPassword() {}

    /**
     * {@inheritDoc}
     */
    public function getRememberToken() {}

    /**
     * {@inheritDoc}
     */
    public function setRememberToken($value) {}

    /**
     * {@inheritDoc}
     */
    public function getRememberTokenName() {}

    /**
     * @return HasMany
     */
    public function perms(): HasMany
    {
        return $this->hasMany(IntegrationPermission::class, 'integration_id', 'id');
    }
}
