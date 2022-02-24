<?php

namespace App\Models\Marketing\Facebook;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Marketplace
 * 
 * @package App\Models\Marketing\Facebook\Marketplace
 */
class Marketplace extends Model
{
    use TableAware;


    // Define Table Name Constant
    const TABLE_NAME = 'fbapp_marketplace';


    /**
     * @const array Account Types
     */
    const ERROR_TYPES = [
        'login' => 'Invalid Credentials',
        'blocked-account' => 'Account Blocked',
        'blocked-marketplace' => 'Marketplace Blocked',
        'failed-post' => 'Inventory Failed to Post'
    ];


    /**
     * @var string
     */
    protected $table = self::TABLE_NAME;

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
        'marketplace_id',
        'inventory_id',
        'action',
        'step',
        'error_type',
        'error_message'
    ];
}