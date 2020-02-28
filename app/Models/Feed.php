<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Feed extends Model
{
    protected $table = 'feed';

    protected $primaryKey = 'id';

    protected $casts = [
        'last_run_at' => 'datetime',
        'filters' => 'json',
        'settings' => 'json',
    ];

    // feed types
    const TYPE_DEALER_OUTGOING_FEED = 'dealer_outgoing_feed';
    const TYPE_DEALER_INCOMING_FEED = 'dealer_incoming_feed';
    const TYPE_FACTORY_FEED = 'factory_feed';
}
