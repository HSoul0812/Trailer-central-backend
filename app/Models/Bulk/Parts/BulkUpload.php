<?php

namespace App\Models\Bulk\Parts;

use Illuminate\Database\Eloquent\Model;

class BulkUpload extends Model {
    
    const VALIDATION_ERROR = 'validation_error';
    const PROCESSING = 'processing';
    const COMPLETE = 'complete';
    
    protected $table = 'parts_bulk_upload';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'dealer_id',
        'status',
        'import_source',
        'validation_errors'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];

    public function getValidationErrors()
    {
        if (empty($this->validation_errors)) {
            return null;
        }
        
        return json_decode($this->validation_errors);
    }
}
