<?php

namespace App\Models\CRM\Documents;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DealerDocuments
 * @package App\Models\CRM\Dealer
 *
 * @property int $id
 * @property int|null $dealer_id
 * @property int|null $lead_id
 * @property string $filename
 * @property string $full_path
 * @property \DateTimeInterface $created_at
 * @property string|null $docusign_path
 * @property string|null $docusign_data
 */
class DealerDocuments extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'dealer_id',
        'lead_id',
        'filename',
        'full_path',
        'docusign_path',
        'docusign_data',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dealer_document_upload';

    const TABLE_NAME = 'dealer_document_upload';

    public static function getTableName(): string
    {
        return self::TABLE_NAME;
    }
}
