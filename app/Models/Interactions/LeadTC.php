<?php

namespace App\Models\Interactions;

use Illuminate\Database\Eloquent\Model;

class LeadTC extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'website_lead';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'identifier';

    /**
     * Get the email history for the lead.
     */
    public function emailHistory()
    {
        return $this->hasMany(EmailHistory::class, 'lead_id', 'identifier');
    }

}
