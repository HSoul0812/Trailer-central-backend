<?php

namespace App\Models\Feed;


use Illuminate\Database\Eloquent\Model;

/**
 * Class TransactionExecuteQueue
 *
 *
 * @package App\Models\Feed
 * @property string $operation_type
 * @property string $data
 * @property date $queued_at
 * @property string $api
 */
class TransactionExecuteQueue extends Model
{
    protected $table = 'transaction_execute_queue';

    protected $primaryKey = 'id';

    public $timestamps = false;
    
    public const OPERATION_TYPES = [
        self::INSERT_OPERATION_TYPE,
        self::UPDATE_OPERATION_TYPE
    ];
            
    public const INSERT_OPERATION_TYPE = 'insert';
    public const UPDATE_OPERATION_TYPE = 'update';
    
    public const SOURCE_MAPPINGS = [
       'pj' => false,
       'btt' => 'bigtex',
       'bttw' => 'trailerworld',
       'bwt' => false,
       'cmtb' => false,
       'olt' => false,
       'pjt' => 'pj',
       'pjtb' => false,
       'tt' => false,
       'ttcom' => false,
       'wcd' => false,
    ];

    protected $fillable = [
        'data',
        'queued_at',
        'api',
        'operation_type'
    ];

}
