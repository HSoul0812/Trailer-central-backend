<?php

namespace App\Models\Bulk\Inventory;

use App\Traits\CompactHelper;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $dealer_id
 * @property string $title
 * @property string $status
 *
 * @method static self find(int $id)
 */
class BulkUpload extends Model {

    const VALIDATION_ERROR = 'validation_error';
    const PROCESSING = 'processing';
    const COMPLETE = 'complete';
    const STATUS_FAILED = 'failed';
    const EXCEPTION_ERROR = 'exception_error';

    protected $table = 'inventory_bulk_upload';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'dealer_id',
        'status',
        'import_source',
        'title',
        'validation_errors',
    ];

    protected $casts = [
        'updated_at' => 'date_format:Y-m-d H:i:s'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];

    /**
     * @return mixed|null
     * @throws \JsonException
     */
    public function getValidationErrors()
    {
        if (empty($this->validation_errors)) {
            return null;
        }

        return json_decode($this->validation_errors, false, 512, JSON_THROW_ON_ERROR);
    }

    public function getIdentifierAttribute()
    {
        return CompactHelper::shorten($this->id);
    }
}
