<?php

namespace App\Util;

use App\Entity\DatasetSubmission;
use GuzzleHttp\Psr7\Stream;

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
    public static function calculateHash(array $inputFileStream, string $algo = DatasetSubmission::SHA256): string
    {
        $fileStream = $inputFileStream['fileStream'] ?? null;
        $context = hash_init($algo);
        hash_update_stream($context, $fileStream);
        return hash_final($context);
    }

    /**
     * Get the file size for the file stream.
     *
     * @param Stream $inputFileStream The file stream.
     *
     * @return integer The file size of the stream.
     */
    public static function getFileSize(Stream $inputFileStream): int
    {
        $fileStream = $inputFileStream->detach();
        $size = 0;
        if (is_resource($fileStream)) {
            $fstat = fstat($fileStream);
            $size = $fstat['size'];
        }

        return $size;
        
    }
}
