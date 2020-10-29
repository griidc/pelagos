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
    public function scanResourceStream($fileHandle)
    {
        if (is_resource($fileHandle)) {
            $stat = fstat($fileHandle);
            if ($stat['size'] > 104857600) {
                $result['status'] = 'failed';
                $result['reason'] = 'oversize';
            } else {
                try {
                    $socket = (new SocketFactory())->createClient($this->clamdSock);
                    $quahog = new QuahogClient($socket);
                    $result = $quahog->scanResourceStream($fileHandle, 1024000);
                } catch (\Exception $e) {
                    $result['status'] = 'failed';
                    $result['reason'] = $e->getMessage();
                }
            }
        } else {
            throw new \Exception('stream not a resource');
        }
        return $result;
    }
}
