<?php

namespace App\Services\Import\Blog;

use App\Repositories\Website\Blog\BulkRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use App\Models\Bulk\Blog\BulkPostUpload;
use App\Repositories\Website\Blog\PostRepositoryInterface;
use Illuminate\Support\Facades\Log;

/**
 *
 *
 * @author Eczek
 */
class CsvImportService implements CsvImportServiceInterface
{

    const MAX_VALIDATION_ERROR_CHAR_COUNT = 3072;

    const TITLE = 'Title';
    const CONTENT = 'Content';
    const URL = 'URL';


    private const S3_VALIDATION_ERRORS_PATH = 'blogs/validation-errors/%s';

    protected $bulkUploadRepository;
    protected $blogRepository;
    protected $bulkPostUpload;

    protected $allowedHeaderValues = [
        self::TITLE => true,
        self::CONTENT => true,
        self::URL => true,
    ];

    /**
     * @var array Array of validation errors per row
     */
    private $validationErrors = [];

    /**
     * @var array Array of CSV headers. Of the form `array[index] = value` where index is the position and value is the header
     */
    private $indexToheaderMapping = [];

    /** @var Log */
    private $log;

    public function __construct(
        BulkRepositoryInterface $bulkUploadRepository,
        PostRepositoryInterface $postRepository
    )
    {
        $this->bulkUploadRepository = $bulkUploadRepository;
        $this->blogRepository = $postRepository;
        $this->log = Log::channel('blog');
    }

    public function run(): bool
    {
        echo "Running...".PHP_EOL;
        $this->log->info('Starting import for bulk upload ID: ' . $this->bulkPostUpload->id);
        echo "Validating...".PHP_EOL;
        try {
           if (!$this->validate()) {
                $this->log->info('Invalid bulk upload ID: ' . $this->bulkPostUpload->id . ' setting validation_errors...');
                $this->bulkUploadRepository->update(['id' => $this->bulkPostUpload->id, 'status' => BulkPostUpload::VALIDATION_ERROR]);
                return false;
            }
        } catch (\Exception $ex) {
            $this->log->info('Invalid bulk upload ID: ' . $this->bulkPostUpload->id . ' setting validation_errors...');
            $this->bulkUploadRepository->update(['id' => $this->bulkPostUpload->id, 'status' => BulkPostUpload::VALIDATION_ERROR]);
            return false;
        }

        echo "Data Valid... Importing...".PHP_EOL;
        $this->log->info('Validation passed for bulk upload ID: ' . $this->bulkPostUpload->id . ' proceeding with import...');
        $this->import();
        return true;
    }

    public function setBulkPostUpload(BulkPostUpload $bulkPostUpload)
    {
        $this->bulkPostUpload = $bulkPostUpload;
    }

    /**
     * Execute the import process
     *
     * @throws \Exception
     */
    protected function import()
    {
        echo "Importing....".PHP_EOL;
        $this->streamCsv(function($csvData, $lineNumber) {
            if ($lineNumber === 1) {
                return;
            }

            echo 'Importing bulk uploaded post on bulk upload : ' . $this->bulkPostUpload->id . ' with data ' . json_encode($csvData).PHP_EOL;
            $this->log->info('Importing bulk uploaded post on bulk upload : ' . $this->bulkPostUpload->id . ' with data ' . json_encode($csvData));

            try {
                // Get Part Data
                $postData = $this->csvToPost($csvData);

                echo "Importing ".json_encode($postData).PHP_EOL;
                $post = $this->blogRepository->create($postData);
                if (!$post) {
                    $this->validationErrors[] = "Image inaccesible";
                    $this->bulkUploadRepository->update(['id' => $this->bulkPostUpload->id, 'status' => BulkPostUpload::VALIDATION_ERROR]);
                    $this->log->info('Error found on post for bulk upload : ' . $this->bulkPostUpload->id . ' : ' . $ex->getTraceAsString());
                    throw new \Exception("Image inaccesible");
                }

            } catch (\Exception $ex) {
                $this->validationErrors[] = $ex->getTraceAsString();
                $this->bulkUploadRepository->update(['id' => $this->bulkPostUpload->id, 'status' => BulkPostUpload::VALIDATION_ERROR]);
                $this->log->info('Error found on blog post for bulk upload : ' . $this->bulkPostUpload->id . ' : ' . $ex->getTraceAsString() . json_encode($this->validationErrors));
                $this->log->info("Index to header mapping: {$this->indexToheaderMapping}");
            }

        });

        if (empty($this->validationErrors)) {
            $this->bulkUploadRepository->update(['id' => $this->bulkPostUpload->id, 'status' => BulkPostUpload::COMPLETE]);
        } else {
             $this->bulkUploadRepository->update(['id' => $this->bulkPostUpload->id, 'status' => BulkPostUpload::COMPLETE]);
        }

    }

    /**
     * Validate the csv file, its headers and content
     *
     * @return bool
     * @throws \Exception
     */
    protected function validate()
    {
        $this->streamCsv(function($csvData, $lineNumber) {
            // for each column
            foreach($csvData as $index => $value) {
                // for the header line
                if ($lineNumber === 1) {
                    // if column header is optional
                    if (!$this->isAllowedHeader($value)) {
                        $this->validationErrors[] = $this->printError($lineNumber, $index + 1, "Invalid Header: ".$value);

                    // else, the column header is allowed
                    } else {
                        $this->allowedHeaderValues[$value] = 'allowed';
                        $this->indexToheaderMapping[$index] = $value;
                    }
                }
            }
        });

        // see if any headers are flagged, return false if so
        foreach($this->allowedHeaderValues as $header => $headerValue) {
            if ($headerValue !== 'allowed') {
                $this->validationErrors[] = $this->printError(1, $header, "Header ".$header." not present.");
            }
        }

        if (count($this->validationErrors) > 0) {
            return false;
        }

        return true;
    }

    /**
     * Applies `$callback($row, $lineNumber)` to each line of the csv
     *
     * @param $callback
     * @throws \Exception
     */
    private function streamCsv($callback)
    {
        $adapter = Storage::disk('s3')->getAdapter();
        $client = $adapter->getClient();
        $client->registerStreamWrapper();

        // open the file from s3
        if (!($stream = fopen("s3://{$adapter->getBucket()}/{$this->bulkPostUpload->import_source}", 'r'))) {
            throw new \Exception('Could not open stream for reading file: ['.$this->bulkPostUpload->import_source.']');
        }

        // iterate through all lines
        $lineNumber = 1;
        while (!feof($stream)) {
             $isEmptyRow = true;
             $csvData = fgetcsv($stream);

             // see if the row is empty,
             // one value is enough to indicate non empty row
             foreach($csvData as $value) {
                 if (!empty($value)) {
                     $isEmptyRow = false;
                     break;
                 }
             }

             // skip empty rows
             if ($isEmptyRow) {
                 continue;
             }

             // apply $callback to the row
             $callback($csvData, $lineNumber++);
             flush();
        }
    }

    private function isAllowedHeader($val)
    {
        return isset($this->allowedHeaderValues[$val]);
    }


    private function printError($line, $column, $errorMessage)
    {
        return "$errorMessage in line $line at column $column";
    }

    private function csvToPost($csvData)
    {
        $formattedImages = [];

        // $keyToIndexMapping[header] basically points to nth column given a column header name
        $keyToIndexMapping = [];
        foreach($this->indexToheaderMapping as $index => $value) {
            $keyToIndexMapping[$value] = $index;
        }

        $post = [];
        $post['dealer_id'] = $this->bulkPostUpload->dealer_id;
        $post['title'] = $csvData[$keyToIndexMapping[self::TITLE]];
        $post['url_path'] = $csvData[$keyToIndexMapping[self::URL]];
        $post['post_content'] = $csvData[$keyToIndexMapping[self::CONTENT]];
        $post['website_id'] = $this->bulkPostUpload->website_id;


        // Return Post Data
        return $post;
    }

    /**
     * Returns true if valid or error message if invalid
     *
     * @param string $type
     * @param string $value
     * @return bool|string
     */
    private function isDataInvalid($type, $value)
    {
        switch($type) {
            case (preg_match(self::TITLE, $type) ? true : false) :
                if (empty($value)) {
                    return "Title cannot be empty.";
                }
                break;
        }

        return false;
    }

    public function outputValidationErrors()
    {
        $jsonEncodedValidationErrors = json_encode($this->validationErrors);
        if (strlen($jsonEncodedValidationErrors) > self::MAX_VALIDATION_ERROR_CHAR_COUNT) {
            $filePath = sprintf(self::S3_VALIDATION_ERRORS_PATH, uniqid().'.txt');
            Storage::disk('s3')->put($filePath, implode(PHP_EOL, $this->validationErrors));
            return json_encode(Storage::disk('s3')->url($filePath));
        }
        return $jsonEncodedValidationErrors;
    }


}
