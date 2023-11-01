<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Xenolope\Quahog\Client as QuahogClient;
use Socket\Raw\Factory as SocketFactory;

class PelagosVirusScanTesterCommand extends Command
{
    protected static $defaultName = 'pelagos:check-virusscan';
    protected static $defaultDescription = 'Test call to ClamAV antivirus engine with EICAR fake virus.';

    /**
     * The ClamAV socket file.
     *
     * @var string
     */
    private $clamdSock;

    /**
     * Class constructor for dependency injection.
     *
     * @param MessageBusInterface $messageBus The messenger bus.
     */
    public function __construct(
        string $clamdSock
    ) {
        $this->clamdSock = $clamdSock;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Keeping test string in rot-13 to prevent University A/V from triggering on this file.
        $eicarPayload = str_rot13('K5B!C%@NC[4\CMK54(C^)7PP)7}$RVPNE-FGNAQNEQ-NAGVIVEHF-GRFG-SVYR!$U+U*');
        $cleanPayload = "Pelagos";

        // Test scan with no virus.
        $this->scan($cleanPayload, $io);

        // Test live virus:
        $this->scan($eicarPayload, $io);

        $io->success('Done!');

        return 0;
    }

    private function scan($payload, $io): void
    {
        try {
            $socket = (new SocketFactory())->createClient($this->clamdSock);
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $io->error("Virus scan failure: $msg");
            return;
        }
        $quahog = new QuahogClient($socket);
        $result = $quahog->scanStream($payload);
        if ($result['status'] == 'OK') {
            $io->success("No virus found in non-virus test.");
        } elseif ($result['status'] == 'FOUND') {
            $io->success("Virus successfully detected in virus live-fire test: " . $result['reason']);
        } else {
            $io->warning("Other virus scan failure: " . $result['reason']);
        }
    }
}
