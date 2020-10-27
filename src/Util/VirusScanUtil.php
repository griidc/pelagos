<?php

namespace App\Util;

use Socket\Raw\Factory as SocketFactory;
use Xenolope\Quahog\Client as QuahogClient;

class VirusScanUtil
{
    /**
     * The ClamAV socket file.
     *
     * @var string clamSock
     */
    private $clamSock;

    /**
     * Constructor for this Controller, to set up default services.
     *
     * @param string                   $clamdSock               String pointing to socket to clamav daemon.
     */
    public function __construct(string $clamdSock)
    {
        $this->clamdSock = $clamdSock;
    }

    /**
     * Scan a filestream for viruses.
     *
     * @param mixed $file      A filestream (file handle) to scan.
     * @return array
     */
    public function Scan($fileStream)
    {
        try {
            $socket = (new SocketFactory())->createClient($this->clamdSock);
            $quahog = new QuahogClient($socket);
            $result = $quahog->scanStream($fileStream);
        } catch (\Exception $e) {
            $result['status'] = 'scanfail';
        }
        return $result;
    }
}
