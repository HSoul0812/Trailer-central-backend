<?php

namespace App\Models\CRM\User;

use App\Utilities\JsonApi\Filterable;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EmailFolder
 * @package App\Models\CRM\User
 */
class EmailFolder extends Model implements Filterable
{
    const TABLE_NAME = 'crm_email_folders';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = self::TABLE_NAME;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'folder_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sales_person_id',
        'user_id',
        'name',
        'date_added',
        'date_imported',
        'failures',
        'failures_since',
        'deleted',
        'error'
    ];

    /**
     * Disable timestamps
     *
     * @var bool
     */
    public $timestamps = false;

    public function salesPerson()
    {
        return $this->belongsTo(SalesPerson::class, 'id', 'sales_person_id');
    }

    public static function getTableName() {
        return self::TABLE_NAME;
    }
}
