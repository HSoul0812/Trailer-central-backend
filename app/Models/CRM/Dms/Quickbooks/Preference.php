<?php

namespace App\Models\CRM\Dms\Quickbooks;

use Illuminate\Database\Eloquent\Model;

/**
 * @author Marcel
 */
class Preference extends Model
{
    protected $table = 'qb_preferences';

    protected $guarded = ['qb_id'];
}
