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

    protected $fillable = [
        'data',
        'queued_at',
        'api'
    ];

}
