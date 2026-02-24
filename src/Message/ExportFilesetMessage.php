<?php

namespace App\Message;

class ExportFilesetMessage
{
    /**
     * Constructor.
     *
     * @param integer $filesetId       Fileset Id it is associated with.
     * @param string  $exportUserEmail Email of the user requesting the export.
     *
     */
    public function __construct(private int $filesetId, private string $exportUserEmail)
    {
        $this->filesetId = $filesetId;
        $this->exportUserEmail = $exportUserEmail;
    }

    /**
     * Get Fileset Id.
     */
    public function getFilesetId(): int
    {
        return $this->filesetId;
    }

    /**
     * Get User Email Address.
     */
    public function getExportUserEmail(): string
    {
        return $this->exportUserEmail;
    }
}
