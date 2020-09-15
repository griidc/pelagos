<?php

namespace App\Util;

use Gedmo\Sluggable\Util\Urlizer;
use League\Flysystem\FilesystemInterface;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class FileUploader
{
    /**
     * Directory prefix.
     *
     * @var string
     */
    private $prefix;

    /**
     * An instance of the Flysystem interface.
     *
     * @var FilesystemInterface
     */
    private $flysystem;

    /**
     * Relative path for chunks folder.
     */
    const CHUNKS_DIRECTORY = 'upload/chunks';

    /**
     * Relative path for files folder.
     */
    const FILES_DIRECTORY = 'upload/files';

    /**
     * FileUploader constructor.
     *
     * @param string              $homedirPrefix    Home directory path prefix.
     * @param FilesystemInterface $uploadFilesystem Upload flysystem file system interface.
     */
    public function __construct(string $homedirPrefix, FilesystemInterface $uploadFilesystem)
    {
        $this->prefix = $homedirPrefix;
        $this->flysystem = $uploadFilesystem;
    }

    /**
     * Uploads chunks to filesystem.
     *
     * @param Request $request Symfony request instance.
     *
     * @throws \Exception When chunk upload fails for a file.
     *
     * @return void
     */
    public function uploadChunk(Request $request): void
    {
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('file');
        $fileExtension = $uploadedFile->guessExtension();
        $fileName = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME) .'.'. $fileExtension;
        $uuid = $request->get('dzuuid');
        $chunksIndex = $request->get('dzchunkindex');
        $chunksFolder = self::CHUNKS_DIRECTORY . DIRECTORY_SEPARATOR . $uuid;
        $stream = fopen($uploadedFile->getPathname(), 'r');
        $result = $this->flysystem->writeStream(
            $chunksFolder . DIRECTORY_SEPARATOR . $chunksIndex,
            $stream
        );

        if ($result === false) {
            throw new \Exception(sprintf('Could not upload chunk for file "%s"', $fileName));
        }

        if (is_resource($stream)) {
            fclose($stream);
        }
    }

    /**
     * To combine all the uploaded chunks into a single file.
     *
     * @param Request $request Symfony request instance.
     *
     * @throws \Exception When combining chunk fails for a file.
     *
     * @return array
     */
    public function combineChunks(Request $request): array
    {
        $uuid = $request->get('dzuuid');
        $fileName = $request->get('fileName');
        $totalChunks = $request->get('dztotalchunkcount');
        $fileSize = $request->get('dztotalfilesize');
        $chunksFolder = $this->prefix . DIRECTORY_SEPARATOR . self::CHUNKS_DIRECTORY . DIRECTORY_SEPARATOR . $uuid;
        $targetDirectory = self::FILES_DIRECTORY . DIRECTORY_SEPARATOR . $uuid;

        for ($i = 0; $i < $totalChunks; $i++) {
            $chunkStream = fopen(
                $chunksFolder .
                DIRECTORY_SEPARATOR .
                $i,
                'rb'
            );
            
            $result = $this->flysystem->putStream(
                $targetDirectory . DIRECTORY_SEPARATOR . $fileName,
                $chunkStream
            );

            if ($result === false) {
                throw new \Exception(sprintf('Could not combine chunk for file "%s"', $fileName));
            }

            if (is_resource($chunkStream)) {
                fclose($chunkStream);
            }

            $this->flysystem->delete(
                self::CHUNKS_DIRECTORY . DIRECTORY_SEPARATOR . $uuid . DIRECTORY_SEPARATOR . $i
            );
        }

        $this->flysystem->deleteDir(self::CHUNKS_DIRECTORY . DIRECTORY_SEPARATOR . $uuid);

        return array(
            'path' => $this->prefix . DIRECTORY_SEPARATOR . $targetDirectory . DIRECTORY_SEPARATOR . $fileName,
            'name' => $fileName,
            'size' => (int)$fileSize,
        );
    }
}
