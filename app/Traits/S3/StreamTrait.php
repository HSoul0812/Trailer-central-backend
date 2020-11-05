<?php

namespace App\Traits\S3;

use Illuminate\Support\Facades\Storage;

trait StreamTrait 
{
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
    protected function stream($callback)
    {          
        $adapter = Storage::disk('s3')->getAdapter();
        $client = $adapter->getClient();
        $client->registerStreamWrapper();
        $stream = $this->getS3Stream();         

        // iterate through all lines
        $lineNumber = 1;
        while (!feof($stream)) {
             $callback($stream, $lineNumber++);
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
