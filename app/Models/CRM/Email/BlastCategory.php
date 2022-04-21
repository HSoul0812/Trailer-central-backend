<?php

namespace App\Models\CRM\Email;

use App\Models\Traits\TableAware;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Email Blast Category
 *
 * @package App\Models\CRM\Email
 */
class BlastCategory extends Model
{
    use TableAware;

    protected $table = 'crm_email_blast_unit_categories';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email_blast_id',
        'category'
    ];

    /**
     * @param int $blastId
     * @return array
     */
    public static function deleteByBlast(int $blastId)
    {
        return self::whereEmailBlastId($blastId)->delete();
    }
}