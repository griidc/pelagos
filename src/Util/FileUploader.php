<?php

namespace App\Util;

use Exception;
use GuzzleHttp\Psr7\Stream;
use GuzzleHttp\Psr7\StreamWrapper;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class FileUploader
{
    /**
     * The directory where uploaded files will be placed.
     *
     * @var string
     */
    private $uploadDirectory;

    /**
     * The directory where file chunks will be stored.
     *
     * @var string
     */
    private $chunksDirectory;

    public function __construct(string $homedirPrefix)
    {
        $this->uploadDirectory = $homedirPrefix . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'files';
        $this->chunksDirectory = $homedirPrefix . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'chunks';
    }

    /**
     * File uploader service.
     *
     * @param Request $request Symfony request instance.
     *
     * @return void
     */
    public function uploadChunk(Request $request): void
    {
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('file');
        $uuid = $request->get('dzuuid');
        $chunksIndex = $request->get('dzchunkindex');
        $chunksFolder = $this->chunksDirectory . DIRECTORY_SEPARATOR . trim($uuid);
        $this->isFolder($chunksFolder);
        $uploadedFile->move(
            $chunksFolder,
            $chunksIndex
        );
    }

    /**
     * Checks if folder exists.
     *
     * @param string $targetFolder Target Folder path.
     *
     * @return void
     */
    private function isFolder(string $targetFolder): void
    {
        if (!file_exists($targetFolder)) {
            mkdir($targetFolder);
            chmod($targetFolder, 0777);
        }
    }

    /**
     * To combine all the uploaded chunks into a single file.
     *
     * @param Request $request Symfony request instance.
     *
     * @throws \Exception
     *
     * @return array
     */
    public function combineChunks(Request $request): array
    {
        $uuid = $request->query->get('dzuuid');
        $fileName = $request->query->get('fileName');
        $totalChunks = $request->query->get('dztotalchunkcount');
        $fileSize = $request->query->get('dztotalfilesize');

        if (empty($fileName) or empty($uuid) or empty($fileSize)) {
            throw new UploadException('Not enough parameters given!');
        }

        $uuid = trim($uuid);

        $chunksFolder = $this->chunksDirectory . DIRECTORY_SEPARATOR . $uuid;
        //combine chunks
        $targetDirectory = $this->uploadDirectory . DIRECTORY_SEPARATOR . $uuid;
        $targetFileName = $targetDirectory . DIRECTORY_SEPARATOR . basename(FileNameUtilities::fixFileNameLength($fileName));
        $this->isFolder($targetDirectory);
        $targetFileStream = new Stream(fopen($targetFileName, 'wb'));
        for ($i = 0; $i < $totalChunks; $i++) {
            $chunk = fopen(
                $chunksFolder .
                DIRECTORY_SEPARATOR .
                $i,
                'rb'
            );
            $targetFile = StreamWrapper::getResource($targetFileStream);
            stream_copy_to_stream($chunk, $targetFile);
            fclose($chunk);
            unlink($chunksFolder . DIRECTORY_SEPARATOR . $i);
        }
        @rmdir($chunksFolder);
        $targetFileSize = $targetFileStream->getSize();
        $targetFileStream->close();
        if ($targetFileSize != $fileSize) {
            unlink($targetFileName);
            rmdir($targetDirectory);
            throw new UploadException(
                'The uploaded file size for file - ' . $fileName . ' ' .
                'does not match expected size. ' .
                'Expected Size - ' . $fileSize . ' bytes, ' .
                'Uploaded Size - ' . $targetFileSize . ' bytes. ' .
                'This file has not been successfully uploaded, please try uploading the file again.'
            );
        }

        // Success
        return array(
            'path' => $targetFileName,
            'name' => $fileName,
            'size' => (int)$fileSize,
        );
    }
}
