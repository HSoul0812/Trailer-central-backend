<?php


namespace App\Models\Feed;


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

    // define constants to make it easier to autocomplete
    // feed types
    const TYPE_DEALER_OUTGOING_FEED = 'dealer_outgoing_feed';
    const TYPE_DEALER_INCOMING_FEED = 'dealer_incoming_feed';
    const TYPE_FACTORY_FEED = 'factory_feed';
    public static $types = [
        self::TYPE_DEALER_OUTGOING_FEED => 'Dealer Outgoing Feed',
        self::TYPE_DEALER_INCOMING_FEED => 'Dealer Incoming Feed',
        self::TYPE_FACTORY_FEED => 'Factory Feed',
    ];

    // data source types
    const DATA_SOURCE_FTP = 'ftp';
    const DATA_SOURCE_HTTP = 'http';
    const DATA_SOURCE_DB = 'database';
    public static $dataSources = [
        self::DATA_SOURCE_FTP => 'FTP',
        self::DATA_SOURCE_HTTP => 'URL',
        self::DATA_SOURCE_DB => 'Database',
    ];

    // status
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    public static $statuses = [
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_INACTIVE => 'Inactive',
    ];

}
