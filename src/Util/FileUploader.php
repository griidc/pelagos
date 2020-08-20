<?php

namespace App\Util;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    private $targetDirectory;

    public function __construct(string $homedirPrefix)
    {
        $this->targetDirectory = $homedirPrefix . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'files';;
    }

    public function upload(UploadedFile $file)
    {

    }
}