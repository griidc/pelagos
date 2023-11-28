<?php

namespace App\Util;

use Psr\Http\Message\StreamInterface;
use ZipStream\CompressionMethod;
use ZipStream\ZipStream;

class ZipFiles
{
    private ?ZipStream $zip = null;

    /**
     * Start the file zip.
     *
     * @param StreamInterface $outputFileStream zip output file stream
     * @param string          $zipFileName      zip file name
     */
    public function start(StreamInterface $outputFileStream, string $zipFileName): void
    {
        $this->zip = new ZipStream(
            outputName: $zipFileName,
            outputStream: $outputFileStream,
            sendHttpHeaders: false,
        );
    }

    /**
     * Add file to the zip.
     *
     * @param string          $fileName   file name of the file that needs to be added to the zip
     * @param StreamInterface $fileStream file stream of the file that needs to be added to the zip
     *
     * @throws \Exception if ZipStream has not been started first
     */
    public function addFile(string $fileName, StreamInterface $fileStream): void
    {
        if (!$this->zip instanceof ZipStream) {
            throw new \Exception('You must start a new ZipStream first!');
        }

        if ($fileStream->isReadable()) {
            $this->zip->addFileFromPsr7Stream(
                fileName: $fileName,
                stream: $fileStream,
                compressionMethod: CompressionMethod::STORE,
            );
        }
    }

    /**
     * Finish zipping the file.
     *
     * @throws \Exception if ZipStream has not been started first
     */
    public function finish(): void
    {
        if (!$this->zip instanceof ZipStream) {
            throw new \Exception('You must start a new zipStream first!');
        }

        $this->zip->finish();
    }
}
