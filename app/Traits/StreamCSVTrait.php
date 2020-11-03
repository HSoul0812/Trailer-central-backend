<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use App\Traits\S3\StreamTrait;

trait StreamCSVTrait {
    
    use StreamTrait;
            
    /**
     * Applies `$callback($row, $lineNumber)` to each line of the csv
     *
     * @param $callback
     * @throws \Exception
     */
    protected function streamCsv($callback)
    {        
        $this->stream(function($stream, $lineNumber) use ($callback) {
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
                 return;
             }

             // apply $callback to the row
             $callback($csvData, $lineNumber++);
        });
    }
    
}
