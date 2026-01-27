<?php

namespace App\Message;

class ExportFilesetMessage
{
    /**
     * The ID of the fileset to be exported.
     *
     * @var integer
     */
    protected $filesetId;

    /**
     * Constructor.
     *
     * @param integer $filesetId Fileset Id it is associated with.
     */
    public function __construct(int $filesetId)
    {
        $this->filesetId = $filesetId;
    }

    /**
     * Getter for filesetId.
     *
     * @return integer
     */
    public function getFilesetId(): int
    {
        return $this->filesetId;
    }
}
