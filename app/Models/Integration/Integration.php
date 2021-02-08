<?php

namespace App\Models\Integration;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Integration
 * @package App\Models\Integration
 */
class Integration extends Model
{
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
}
