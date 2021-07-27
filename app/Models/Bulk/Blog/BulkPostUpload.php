<?php

namespace App\Models\Bulk\Blog;

use App\Models\Bulk\Parts\BulkUpload;

class BulkPostUpload extends BulkUpload {

    protected $table = 'blog_bulk_upload';

    protected $fillable = [
        'dealer_id',
        'status',
        'import_source',
        'validation_errors',
        'website_id',
    ];
}
