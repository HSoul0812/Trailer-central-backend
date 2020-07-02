<?php

namespace App\Models\CRM\Text;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Text Campaign Category
 *
 * @package App\Models\CRM\Text
 */
class CampaignCategory extends Model
{
    protected $table = 'crm_text_campaign_category';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'text_campaign_id',
        'category'
    ];
}