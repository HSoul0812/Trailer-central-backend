<?php


namespace App\Models\CRM\Interactions;

use App\Models\CRM\Leads\Lead;
use Illuminate\Database\Eloquent\Model;

class TextLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dealer_texts_log';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'identifier', 'id');
    }
}
