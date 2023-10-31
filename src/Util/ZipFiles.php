<?php

namespace App\Util;

use Exception;
use Psr\Http\Message\StreamInterface;
use ZipStream\CompressionMethod;
use ZipStream\ZipStream;

class ZipFiles
{
    private ?ZipStream $zip = null;

    /**
     * Start the file zip.
     *
     * @param StreamInterface  $outputFileStream Zip output file stream.
     * @param string           $zipFileName      Zip file name.
     *
     * @return void
     */
    public function start(StreamInterface $outputFileStream, string $zipFileName): void
    {
        $this->zip = new ZipStream(
            outputName: $zipFileName,
            outputStream: $outputFileStream,
        );
    }

    /**
     * Add file to the zip.
     *
     * @param string           $fileName   File name of the file that needs to be added to the zip.
     * @param StreamInterface  $fileStream File stream of the file that needs to be added to the zip.
     *
     * @throws \Exception If ZipStream has not been started first.
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
     * @throws Exception If ZipStream has not been started first.
     */
    public function finish(): void
    {
        if (!$this->zip instanceof ZipStream) {
            throw new \Exception('You must start a new zipStream first!');
        }

        $this->zip->finish();
    }
}
