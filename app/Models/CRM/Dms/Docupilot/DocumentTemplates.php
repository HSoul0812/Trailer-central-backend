<?php

namespace App\Models\CRM\Dms\Docupilot;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DocumentTemplates
 * @package App\Models\CRM\Dms\Docupilot
 *
 * @property int $id
 * @property int $template_id
 * @property int $dealer_id
 * @property string $type
 * @property string $type_quote
 * @property string $type_deal
 * @property string $type_service
 */
class DocumentTemplates extends Model
{
    protected $table = 'dms_document_templates';

    public $timestamps = false;

    protected $fillable = [
        "type_quote",
        "type_deal",
        "type",
        "type_service",
    ];
}
