<?php

namespace App\Util;

use Psr\Http\Message\StreamInterface;
use Socket\Raw\Factory as SocketFactory;
use Xenolope\Quahog\Client as QuahogClient;

class VirusScanUtil
{
    /**
     * The ClamAV socket file.
     *
     * @var string
     */
    private $clamdSock;

    /**
     * Scan result status if it fails.
     */
    const RESULT_STATUS_FAILED = 'failed';

    /**
     * Scan result reason when it is oversize.
     */
    const RESULT_REASON_OVERSIZE = 'oversize';

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
     * @return array
     */
    public function scanResourceStream(StreamInterface $stream): array
    {
        $result = array();
        $fileSize = $stream->getSize();
        if ($fileSize > 104857600) {
            $result['status'] = self::RESULT_STATUS_FAILED;
            $result['reason'] = self::RESULT_REASON_OVERSIZE;
        } else {
            try {
                $socket = (new SocketFactory())->createClient($this->clamdSock);
                $quahog = new QuahogClient($socket);
                $resource = $stream->detach();
                $result = $quahog->scanResourceStream($resource, 1024000);
            } catch (\Exception $e) {
                $result['status'] = self::RESULT_STATUS_FAILED;
                $result['reason'] = $e->getMessage();
            }
        }
        return $result;
    }
}
