<?php

namespace App\Models\CRM\Text;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Text Blast Category
 *
 * @package App\Models\CRM\Text
 */
class BlastCategory extends Model
{
    protected $table = 'crm_text_blast_category';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'text_blast_id',
        'category'
    ];

    /**
     * @param int $blastId
     * @return array
     */
    public static function deleteByBlast(int $blastId)
    {
        return self::whereTextBlastId($blastId)->delete();
    }
}