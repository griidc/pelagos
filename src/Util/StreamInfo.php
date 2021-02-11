<?php

namespace App\Util;

/**
 * A utility class to get various information from a file stream.
 */
class StreamInfo
{
    /**
     * Get the hash for file stream.
     *
     * @param array $fileStream The file stream array.
     *
     * @return string The hash calculated from the stream.
     */
    public static function calculateHash(array $inputFileStream, string $algo = 'sha256') :string
    {
        $fileStream = $inputFileStream['fileStream'] ?? null;
        $context = hash_init($algo);
        hash_update_stream($context, $fileStream);
        return hash_final($context);
    }

    /**
     * Get the file size for the file stream.
     *
     * @param array $fileStream The file stream array.
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
