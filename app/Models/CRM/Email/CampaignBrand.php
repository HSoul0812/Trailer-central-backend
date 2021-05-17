<?php

namespace App\Models\CRM\Email;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Email Campaign Brand
 *
 * @package App\Models\CRM\Email
 */
class CampaignBrand extends Model
{
    protected $table = 'crm_email_campaign_unit_brands';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email_campaign_id',
        'brand'
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