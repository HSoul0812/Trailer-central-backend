<?php

namespace App\Models\CRM\Email;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Email Campaign Category
 *
 * @package App\Models\CRM\Email
 */
class CampaignCategory extends Model
{
    protected $table = 'crm_email_campaign_unit_categories';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email_campaign_id',
        'category'
    ];

    /**
     * @param int $campaignId
     * @return array
     */
    public static function deleteByCampaign(int $campaignId)
    {
        return self::whereEmailCampaignId($campaignId)->delete();
    }
}