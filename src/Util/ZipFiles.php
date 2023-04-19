<?php

namespace App\Util;

use Psr\Http\Message\StreamInterface;
use ZipStream\CompressionMethod;
use ZipStream\ZipStream;

class ZipFiles
{
    /**
     * @var ZipStream
     */
    private $zip;

    /**
     * Make a new zip file.
     */
    public function __construct(StreamInterface $outputFileStream, string $zipFileName)
    {
        $this->zip = new ZipStream(
            outputName: $zipFileName,
            outputStream: $outputFileStream,
        );
    }

    /**
     * Add file to the zip.
     *
     * @param string            $fileName          file name of the file that needs to be zipped
     * @param StreamInterface   $fileStream        file stream of the file that needs to be zipped
     * @param CompressionMethod $compressionMethod The storage method for this file (default store)
     */
    public function addFile(string $fileName, StreamInterface $fileStream, CompressionMethod $compressionMethod = CompressionMethod::STORE): void
    {
        $this->zip->addFileFromPsr7Stream(
            fileName: $fileName,
            stream: $fileStream,
            compressionMethod: $compressionMethod
        );
    }

    /**
     * Finish zipping the file.
     */
    public function finish(): void
    {
        $this->zip->finish();
    }
}
