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
     * @param string $clamdSock String pointing to socket to clamav daemon.
     */
    public function __construct(string $clamdSock)
    {
        $this->clamdSock = $clamdSock;
    }

    /**
     * Scan a filestream for viruses.
     *
     * @param mixed $fileHandle A filesystem resource to scan.
     * @return array
     */
    public function ScanResourceStream($fileHandle)
    {
        try {
            $socket = (new SocketFactory())->createClient($this->clamdSock);
            $quahog = new QuahogClient($socket);
            $result = $quahog->scanResourceStream($fileHandle, 1024000);
        } catch (\Exception $e) {
            $result['status'] = 'scanfail';
            $result['reason'] = $e->getMessage();
        }
        return $result;
    }
}
