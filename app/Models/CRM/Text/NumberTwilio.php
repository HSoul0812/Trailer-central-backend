<?php

namespace App\Models\CRM\Text;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Text Number Twilio
 *
 * @package App\Models\CRM\Text
 */
class NumberTwilio extends Model
{
    use TableAware;

    /**
     * @var string
     */
    const TABLE_NAME = 'twilio_numbers';

    protected $table = self::TABLE_NAME;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'phone_number';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'phone_number'
    ];

    // No Timestamps
    public $timestamps = false;
}