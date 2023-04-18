<?php

namespace App\Util;

use GuzzleHttp\Psr7\Stream;
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
    public function __construct(Stream $outputFileStream, string $zipFileName)
    {
        $this->zip = new ZipStream(
            outputName: $zipFileName,
            outputStream: $outputFileStream,
        );
    }

    /**
     * Add file to the zip.
     *
     * @param string $fileName   file name of the file that needs to be zipped
     * @param Stream $fileStream file stream of the file that needs to be zipped
     */
    public function addFile(string $fileName, Stream $fileStream, CompressionMethod $compressionMethod = CompressionMethod::STORE): void
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
