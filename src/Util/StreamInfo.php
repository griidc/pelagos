<?php

namespace App\Util;

use App\Entity\DatasetSubmission;
use GuzzleHttp\Psr7\Utils as GuzzlePsr7Utils;
use Psr\Http\Message\StreamInterface;

/**
 * A utility class to get various information from a file stream.
 */
class StreamInfo
{
    /**
     * Get the hash for file stream.
     *
     * @param StreamInterface $inputFileStream The file stream.
     * @param array           $algo            The algorithm for the hash function (default SHA256).
     *
     * @return string The hash calculated from the stream.
     */
    public static function calculateHash(StreamInterface $inputFileStream, string $algo = DatasetSubmission::SHA256) :string
    {
        return GuzzlePsr7Utils::hash($inputFileStream, $algo);
    }

    /**
     * Get the file size for the file stream.
     *
     * @param StreamInterface $inputStream The file stream.
     *
     * @return integer The file size of the stream.
     */
    public static function getFileSize(StreamInterface $inputFileStream): ?int
    {
        return $inputFileStream->getSize();
    }
}
