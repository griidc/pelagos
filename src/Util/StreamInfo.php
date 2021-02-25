<?php

namespace App\Util;

use App\Entity\DatasetSubmission;

/**
 * A utility class to get various information from a file stream.
 */
class StreamInfo
{
    /**
     * Get the hash for file stream.
     *
     * @param array $inputFileStream The file stream array.
     * @param array $algo            The algorithm for the hash function (default SHA256).
     *
     * @return string The hash calculated from the stream.
     */
    public static function calculateHash(array $inputFileStream, string $algo = DatasetSubmission::SHA256) :string
    {
        $fileStream = $inputFileStream['fileStream'] ?? null;
        
        dump(ftell($fileStream));
        
        rewind($fileStream);
        
        $context = hash_init($algo);
        hash_update_stream($context, $fileStream);
        $hash =  hash_final($context);
        
        dump($hash);
        
        return $hash;
    }

    /**
     * Get the file size for the file stream.
     *
     * @param array $inputFileStream The file stream array.
     *
     * @return integer The file size of the stream.
     */
    public static function getFileSize(array $inputFileStream) :int
    {
        $fileStream = $inputFileStream['fileStream'] ?? null;
        $fstat = fstat($fileStream);
        return (int) $fstat['size'];
    }
}
