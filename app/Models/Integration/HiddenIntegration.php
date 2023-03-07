<?php

namespace App\Models\Integration;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Integration
 * @package App\Models\Integration
 *
 * @property int $id
 * @property int $integration_id
 * @property boolean $is_hidden
 */
class HiddenIntegration extends Model
{
    // Define Table Name Constant
    const TABLE_NAME = 'hidden_integrations';

    /**
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * @var string
     */
    protected $primaryKey = 'id';

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
        'is_hidden'
    ];
}
