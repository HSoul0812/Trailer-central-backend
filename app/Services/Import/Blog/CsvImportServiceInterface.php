<?php

namespace App\Services\Import\Blog;

use App\Models\Bulk\Blog\BulkPostUpload;

/**
 *
 * @author Eczek
 */
interface CsvImportServiceInterface extends \App\Services\Import\CsvImportInterface {
    public function run(): bool;
    public function setBulkPostUpload(BulkPostUpload $bulkPostUpload);
}
