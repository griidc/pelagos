<?php

namespace App\Message;

class DeleteFile
{
    /**
     * The file path of the file to be deleted.
     *
     * @var string
     */
    protected $filePath;

    /**
     * A boolean flag to show that the file is in the store.
     *
     * @var boolean
     */
    protected $inStore;

    /**
     * Constructor.
     *
     * @param string $filePath The file path of the file to be deleted.
     */
    public function __construct(string $filePath, bool $inStore = true)
    {
        $this->filePath = $filePath;
        $this->inStore = $inStore;
    }

    /**
     * The file path getter.
     *
     * @return string The file path.
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * Is the file not in the store.
     *
     * @return string The file path.
     */
    public function isInStore(): bool
    {
        return $this->inStore;
    }
}
