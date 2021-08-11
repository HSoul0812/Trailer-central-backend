<?php

namespace App\Models\CRM\Dms;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CvrCreds
 * @package App\Models\CRM\Dms
 * @property int $dealer_id
 * @property string $cvr_username
 * @property string $cvr_unique_id
 * @property string $cvr_password
 */
class CvrCreds extends Model {
    
    protected $table = 'dms_cvr_creds';

    protected $fillable = [
        'dealer_id',
        'cvr_username',
        'cvr_password'
    ];
    
}
