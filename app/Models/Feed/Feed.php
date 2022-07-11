<?php


namespace App\Models\Feed;


use Illuminate\Database\Eloquent\Model;

/**
 * Class Feed
 *
 * Represents a configured feed. Feeds are run every X interval
 *
 * @package App\Models\Feed
 */
class Feed extends Model
{
    protected $table = 'feed';

    protected $primaryKey = 'id';

    protected $casts = [
        'last_run_start' => 'datetime',
        'last_run_end' => 'datetime',
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
    const STATUS_DISABLED = 'disabled';
    
    public static $statuses = [
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_DISABLED => 'Disabled',
    ];

    // Run status
    const RUN_STATUS_IDLE = 'idle';
    const RUN_STATUS_RUNNING = 'running';
    const RUN_STATUS_PAUSED = 'paused';
    const RUN_STATUS_ERROR = 'error';
    const RUN_STATUS_SUCCESS = 'success';
    
    public static $runStatuses = [
        self::RUN_STATUS_IDLE => 'Idle',
        self::RUN_STATUS_RUNNING => 'Running',
        self::RUN_STATUS_PAUSED => 'Paused',
        self::RUN_STATUS_ERROR => 'Error',
        self::RUN_STATUS_SUCCESS => 'Success',
    ];

    public function setDataSourceParamsAttribute($value)
    {
        $this->attributes['data_source_params'] = json_encode($value);
    }

    public function setNotifyEmailAttribute($value)
    {
        $this->attributes['notify_email'] = json_encode($value);
    }

    public function setSettingsAttribute($value)
    {
        $this->attributes['settings'] = json_encode($value);
    }

    public function getDataSourceParamsAttribute()
    {
        return json_decode(@$this->attributes['data_source_params']);
    }

    public function getNotifyEmailAttribute()
    {
        return json_decode(@$this->attributes['notify_email']);
    }

    public function getSettingsAttribute()
    {
        return json_decode(@$this->attributes['settings']);
    }

}
