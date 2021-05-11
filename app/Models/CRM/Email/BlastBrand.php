<?php

namespace App\Models\CRM\Email;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Email Blast Brand
 *
 * @package App\Models\CRM\Email
 */
class BlastBrand extends Model
{
    protected $table = 'crm_email_blast_unit_brands';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email_blast_id',
        'brand'
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