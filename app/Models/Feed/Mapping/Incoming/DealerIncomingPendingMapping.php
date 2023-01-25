<?php

namespace App\Models\Feed\Mapping\Incoming;

use Illuminate\Database\Eloquent\Model;

class DealerIncomingPendingMapping extends Model {

    protected $table = 'dealer_incoming_pending_mapping';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'dealer_id',
        'type',
        'data'
    ];

    const MAKE = 'manufacturer';
    const CATEGORY = 'category';
    const ENTITY_TYPE = 'entity_type';
    const CONDITION = 'condition';
    const STATUS = 'status';
    const PULL_TYPE = 'pull_type';
    const NOSE_TYPE = 'nose_type';
    const CONSTRUCTION = 'construction';
    const FUEL_TYPE = 'fuel_type';

    public static $types = [
        self::MAKE => 'Manufacturer',
        self::CATEGORY => 'Category',
        self::ENTITY_TYPE => 'Entity Type',
        self::CONDITION => 'Condition',
        self::STATUS => 'Status',
        self::PULL_TYPE => 'Pull Type',
        self::NOSE_TYPE => 'Nose Type',
        self::CONSTRUCTION => 'Construction',
        self::FUEL_TYPE => 'Fuel Type'
    ];

}
