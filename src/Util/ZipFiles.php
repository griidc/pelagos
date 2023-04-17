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
     * Start the file zip.
     */
    public function start(Stream $outputFileStream, string $zipFileName): void
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
    public function addFile(string $fileName, Stream $fileStream): void
    {
        $this->zip->addFileFromPsr7Stream(
            fileName: $fileName,
            stream: $fileStream,
            compressionMethod: CompressionMethod::STORE
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
