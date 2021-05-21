<?php

namespace App\Models\CRM\Dms\Printer;

use App\Models\CRM\Dms\Printer\Form;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Instructions
 * 
 * @package App\Models\CRM\Dms\Printer
 */
class Instructions extends Model
{
    public const TABLE_NAME = "dms_printer_form_instructions";

    protected $table = self::TABLE_NAME;

    protected $fillable = [
        'form_id',
        'font_size',
        'x_position',
        'y_position'
    ];

    public static function getTableName(): string
    {
        return self::TABLE_NAME;
    }
    
    /**
     * Get Form
     *
     * @return BelongsTo
     */
    public function form(): BelongsTo {
        return $this->belongsTo(Form::class);
    }
}
