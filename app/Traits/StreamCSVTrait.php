<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait StreamCSVTrait {
    
    /**     
     * @var string
     */
    protected $s3Bucket;
    
    /**     
     * @var string
     */
    protected $s3Key;
        
    /**
     * Applies `$callback($row, $lineNumber)` to each line of the csv
     *
     * @param $callback
     * @throws \Exception
     */
    protected function streamCsv($callback)
    {        
        $adapter = Storage::disk('s3')->getAdapter();
        $client = $adapter->getClient();
        $client->registerStreamWrapper();
        $stream = $this->getS3Stream();         

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
    
    private function getS3Stream()
    {
        if (empty($this->s3Bucket)) {
            throw new \Exception("s3 Bucket cannot be empty");
        }
        
        if (empty($this->s3Key)) {
            throw new \Exception("s3 Key cannot be empty");
        }
        
        // open the file from s3
        if (!($stream = fopen("s3://{$this->s3Bucket}/{$this->s3Key}", 'r'))) {
            throw new \Exception('Could not open stream for reading file: ['.$this->s3Key.']');
        }
        
        return $stream;
    }
    
}
