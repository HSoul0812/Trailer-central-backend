<?php

namespace App\Models\Integration\Facebook;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class ChatSalesPeople
 * @package App\Models\Integration\Facebook
 */
class ChatSalesPeople extends Pivot
{
    // Define Table Name Constant
    const TABLE_NAME = 'fbapp_chat_salespeople';

    /**
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * @var string
     */
    protected $primaryKey = 'id';
    
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;
}