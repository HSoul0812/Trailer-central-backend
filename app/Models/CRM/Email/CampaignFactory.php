<?php

namespace App\Models\CRM\Email;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

/**
 *
 * @package App\Models\CRM\Email
 */
class CampaignFactory extends Model
{
    use TableAware;

    const TABLE_NAME = 'crm_factory_campaign';

    protected $table = self::TABLE_NAME;

}