<?php

namespace App\Models\Interactions;

use Illuminate\Database\Eloquent\Model;

class Interaction extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'crm_interaction';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'interaction_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "lead_product_id",
        "tc_lead_id",
        "user_id",
        "interaction_type",
        "interaction_notes",
        "interaction_time"
    ];

    /**
     * Get the email history for the interaction.
     */
    public function emailHistory()
    {
        return $this->hasMany(EmailHistory::class, 'interaction_id', 'interaction_id');
    }

    /**
     * Get the Lead for the interaction.
     */
    public function lead()
    {
        return $this->belongsTo(LeadTC::class, 'tc_lead_id', 'identifier');
    }
}
