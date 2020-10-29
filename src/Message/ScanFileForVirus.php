<?php

namespace App\Message;

/**
 * Message for virus-scanning files.
 */
class ScanFileForVirus
{
    /**
     * The stream to be scanned.
     *
     * @var resource
     */
    protected $stream;

    /**
     * The UDI of the associated stream, for identification purposes.
     *
     * @var string udi
     */
    protected $udi;

    /**
     * The ID of the file assocated with the stream, for identification purposes.
     *
     * @var int id
     */
    protected $id;

    /**
     * Constructor.
     *
     * @param array    The stream and attributes describing the associated file for who's stream is to be scanned.
     * @param string   The UDI associted with the stream.
     * @param int      The fileId associated with the stream.
     */
    public function __construct(array $stream, string $udi, int $id)
    {
        $this->stream = $stream;
        $this->udi = $udi;
        $this->id = $id;
    }

    /**
     * The stream getter.
     *
     * @return array The filehandle to be scanned.
     */
    public function getStream(): array
    {
        return $this->stream;
    }

    /**
     * The Udi getter.
     *
     * @return string The UDI associated with the stream.
     */
    public function getUdi(): string
    {
        return $this->udi;
    }

    /**
     * The file ID getter.
     *
     * @return string The fileId associated with the stream.
     */
    public function getId(): int
    {
        return $this->id;
    }
}
