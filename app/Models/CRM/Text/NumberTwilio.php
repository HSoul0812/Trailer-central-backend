<?php

namespace App\Models\CRM\Text;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Text Number Twilio
 *
 * @package App\Models\CRM\Text
 */
class NumberTwilio extends Model
{
    protected $table = 'twilio_numbers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'phone_number'
    ];

    // No Timestamps
    protected $timestamps = false;
}