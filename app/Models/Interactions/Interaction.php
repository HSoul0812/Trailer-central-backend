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
     * Get the email history for the interaction.
     */
    public function emailHistory()
    {
        return $this->hasMany(EmailHistory::class, 'interaction_id', 'interaction_id');
    }
}
