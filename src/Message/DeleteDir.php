<?php

namespace App\Message;

class DeleteDir
{
    /**
     * Dataset udi associated with it.
     *
     * @var string
     */
    protected $udi;

    /**
     * Path of the folder that needs to be removed from disk.
     *
     * @var string
     */
    protected $dirPath;

    /**
     * Constructor.
     *
     * @param string $udi     Associated Dataset udi.
     * @param string $dirPath The path of the folder that needs to be removed.
     */
    public function __construct(string $udi, string $dirPath)
    {
        $this->udi = $udi;
        $this->dirPath = $dirPath;
    }

    /**
     * The folder path getter.
     *
     * @return string The folder path.
     */
    public function getDeleteDirPath(): string
    {
        return $this->dirPath;
    }

    /**
     * Dataset udi getter.
     *
     * @return string
     */
    public function getDatasetUdi(): string
    {
        return $this->udi;
    }
}
