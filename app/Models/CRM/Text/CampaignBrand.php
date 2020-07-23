<?php

namespace App\Models\CRM\Text;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Text Campaign Brand
 *
 * @package App\Models\CRM\Text
 */
class CampaignBrand extends Model
{
    protected $table = 'crm_text_campaign_brand';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'text_campaign_id',
        'brand'
    ];

    /**
     * @param int $campaignId
     * @return array
     */
    public static function deleteByCampaign(int $campaignId)
    {
        return self::whereTextCampaignId($campaignId)->delete();
    }
}