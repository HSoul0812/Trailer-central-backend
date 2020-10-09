<?php

namespace App\Models\Integration\Auth;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Scope
 * @package App\Models\Integration\Auth
 */
class Scope extends Model
{
    // Define Table Name Constant
    const TABLE_NAME = 'integration_token_scopes';

    /**
     * @var string
     */
    protected $table = TABLE_NAME;

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'integration_token_id',
        'scope'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function token()
    {
        return $this->belongsTo(AccessToken::class);
    }
}
