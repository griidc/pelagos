<?php

namespace App\Util;

use App\Repository\FilesetRepository;

class FolderStructureGenerator
{

    /**
     * An instance of a Fileset Repository.
     *
     * @var FilesetRepository
     */
    protected $filesetRepository;

    /**
     * Class constructor for FolderStructureGenerator.
     *
     * @param FilesetRepository $filesetRepository Fileset Repository instance.
     */
    public function __construct(FilesetRepository $filesetRepository)
    {
        $this->filesetRepository = $filesetRepository;
    }

    /**
     * Get folder and file structure in json for File Manager.
     *
     * @param integer $filesetId Fileset id for the files.
     * @param string  $path      Directory path for files and folders.
     *
     * @return array
     */
    public function getFolderJson(int $filesetId, string $path = '') : array
    {
        $fileset = $this->filesetRepository->find($filesetId);
        $folderJson = array();
        foreach ($fileset->getFilesInDirectory($path) as $file) {
            $filePathParts = $file->getFilePathParts($path);
            $isDir = count($filePathParts) > 1;
            if (!in_array($filePathParts[0], array_column($folderJson, 'name'))) {
                if (!$isDir) {
                    $folderJson[] = array(
                        'name' => $filePathParts[0],
                        'isDirectory' => false,
                        'size' => $file->getFileSize(),
                        'dateModified' => $file->getUploadedAt()->format('m/j/Y')
                    );
                } else {
                    $folderJson[] = array(
                        'name' => $filePathParts[0],
                        'isDirectory' => true,
                        'hasSubDirectories' => (count($filePathParts) > 2)
                    );
                }
            } elseif (count($filePathParts) > 2) {
                $index = array_search($filePathParts[0], array_column($folderJson, 'name'));
                $folderJson[$index]['hasSubDirectories'] = true;
            }
        }
        return $folderJson;
    }
}
