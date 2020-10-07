<?php

namespace App\Models\Integration\Auth;

use App\Models\Inventory\Inventory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class DealerInventory
 * @package App\Models\Integration\LotVantage
 */
class AccessToken extends Model
{
    // Define Table Name Constant
    const TABLE_NAME = 'integration_tokens';

    // Define Token Types
    const TOKEN_TYPES = [
        'google',
        'facebook'
    ];

    // Define Relation Types
    const RELATION_TYPES = [
        'sales_person',
        'fbapp_page'
    ];

    /**
     * @var string
     */
    protected $table = TABLE_NAME;

    /**
     * @var string
     */
    protected $primaryKey = 'id';
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function scopes()
    {
        return $this->hasMany(Scope::class);
    }
}
