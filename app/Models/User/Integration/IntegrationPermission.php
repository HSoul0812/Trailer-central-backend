<?php

namespace App\Models\User\Integration;

use Illuminate\Database\Eloquent\Model;

/**
 * Class IntegrationPermission
 * @package App\Models\User\Integration
 *
 * @property integer $id
 * @property integer $integration_id
 * @property string $feature
 * @property string $permission_level
 * @property \DateTimeInterface $created_at
 * @property \DateTimeInterface $updated_at
 */
class IntegrationPermission extends Model
{
    protected $table = 'interaction_integration_permission';

    protected $fillable = [
        'integration_id',
        'feature',
        'permission_level',
    ];
}
