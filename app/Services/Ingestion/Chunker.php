<?php

namespace App\Services\Ingestion;

class Chunker
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function chunk(string $content , int $maxChars = 1500 , int $overlap = 200): array{
    
        if(strlen($content) <= $maxChars){
            return [$content];
        }

        $chunks = [];
        $start = 0;
        $length = strlen($content);

        while($start < $length){
            $end = min($start + $maxChars, $length);
            $chunk = substr($content, $start, $end - $start);
            $chunks[] = $chunk;
            $start += ($maxChars - $overlap);
        }

        return $chunks;

    }

}
