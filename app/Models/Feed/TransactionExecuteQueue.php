<?php

namespace App\Models\Feed;


use Illuminate\Database\Eloquent\Model;

/**
 * Class TransactionExecuteQueue
 *
 *
 * @package App\Models\Feed
 */
class TransactionExecuteQueue extends Model
{
    protected $table = 'transaction_execute_queue';

    protected $primaryKey = 'id';

    public $timestamps = false;
    
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
        'api'
    ];

}
