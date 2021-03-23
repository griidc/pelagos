<?php

namespace App\Util;

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
     * @return array
     */
    public function combineChunks(Request $request): array
    {
        $uuid = trim($request->get('dzuuid'));
        $fileName = $request->get('fileName');
        $totalChunks = $request->get('dztotalchunkcount');
        $fileSize = $request->get('dztotalfilesize');

        $chunksFolder = $this->chunksDirectory . DIRECTORY_SEPARATOR . $uuid;
        //combine chunks
        $targetDirectory = $this->uploadDirectory . DIRECTORY_SEPARATOR . $uuid;
        $this->isFolder($targetDirectory);
        $targetFile = fopen($targetDirectory . DIRECTORY_SEPARATOR . basename($fileName), 'wb');
        for ($i = 0; $i < $totalChunks; $i++) {
            $chunk = fopen(
                $chunksFolder .
                DIRECTORY_SEPARATOR .
                $i,
                'rb'
            );
            stream_copy_to_stream($chunk, $targetFile);
            fclose($chunk);
            unlink($chunksFolder . DIRECTORY_SEPARATOR . $i);
        }
        // Success
        fclose($targetFile);
        rmdir($chunksFolder);

        return array(
            'path' => $targetDirectory . DIRECTORY_SEPARATOR . basename($fileName),
            'name' => $fileName,
            'size' => (int)$fileSize,
        );
    }
}
