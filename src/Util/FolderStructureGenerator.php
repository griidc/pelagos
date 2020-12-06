<?php

namespace App\Util;

use App\Repository\FileRepository;
use App\Repository\FilesetRepository;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use SplMinHeap;

class FolderStructureGenerator
{

    /**
     * An instance of a Fileset Repository.
     *
     * @var FilesetRepository
     */
    protected $filesetRepository;

    /**
     * An instance of a File Repository.
     *
     * @var FileRepository
     */
    protected $fileRepository;

    /**
     * Limiting the string replace to first occurrence.
     */
    const REPLACE_NUM_OF_OCCURRENCES = 1;

    /**
     * Class constructor for FolderStructureGenerator.
     *
     * @param FilesetRepository $filesetRepository Fileset Repository instance.
     * @param FileRepository    $fileRepository    File Repository instance.
     */
    public function __construct(FilesetRepository $filesetRepository, FileRepository $fileRepository)
    {
        $this->filesetRepository = $filesetRepository;
        $this->fileRepository = $fileRepository;
    }

    /**
     * Get folder and file structure in json for File Manager.
     *
     * @param integer $filesetId Fileset id for the files.
     * @param string $path
     *
     * @return array
     */
    public function getFolderJson(int $filesetId, string $path = '') : array
    {
        $fileset = $this->filesetRepository->find($filesetId);
        $folderJson = array();
        $folders = array();
        foreach ($fileset->getFilesInDirectory($path) as $file) {
            if ($path) {
                $count = self::REPLACE_NUM_OF_OCCURRENCES;
                $filePathParts = explode('/', str_replace($path, '', $file->getFilePathName(), $count));
                array_shift($filePathParts);
                $folders[$file->getId()] = $filePathParts;
            } else {
                $folders[$file->getId()] = explode('/', $file->getFilePathName());
            }
        }

        foreach ($folders as $fileId => $filePathArray) {
            $isDir = count($filePathArray) > 1;
            if (!empty($folderJson) and $folderJson[0]['name'] === $filePathArray[0]) {
                continue;
            } else {
                if (!$isDir) {
                    $file = $this->fileRepository->find($fileId);
                    $folderJson[] = array(
                        'name' => $filePathArray[0],
                        'isDirectory' => false,
                        'size' => $file->getFileSize(),
                        'dateModified' => $file->getUploadedAt()->format('m/d/Y')
                    );
                } else {
                    $folderJson[] = array(
                        'name' => $filePathArray[0],
                        'isDirectory' => true,
                    );
                }
            }
        }

        return $folderJson;
    }
}
