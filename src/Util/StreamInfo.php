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
     * @param string $algo   The algorithm for the hash function (default SHA256).
     */
    public static function calculateHash(StreamInterface $stream, string $algo = DatasetSubmission::SHA256): string
    {
        return GuzzlePsr7Utils::hash($stream, $algo);
    }

    /**
     * Get the file size for the file stream.
     */
    public static function getFileSize(StreamInterface $stream): ?int
    {
        return $stream->getSize();
    }
}
