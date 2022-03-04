<?php

namespace App\Command;

use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\File;
use App\Entity\Fileset;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PelagosClearErrorAndFileCommand extends Command
{
    protected static $defaultName = 'pelagos:clear-error-file';
    protected static $defaultDescription = 'Removes an error-status file from fileset and if exists, filesystem.';

    /**
     * A Doctrine ORM EntityManager instance.
     *
     * @var EntityManagerInterface $entityManager
     */
    protected $entityManager;

    /**
     * Class constructor for dependency injection.
     *
     * @param EntityManagerInterface $entityManager A Doctrine EntityManager.
     */
    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;

        // It is required to call parent constructor if
        // using a constructon in a Symfony command.
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('fileId', InputArgument::REQUIRED, 'ID of file in error to delete')
            ->addArgument('udi', InputArgument::REQUIRED, 'UDI of the dataset containing file to delete.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fileId = $input->getArgument('fileId');
        $udi = $input->getArgument('udi');

        $dataset = $this->entityManager->getRepository(Dataset::class)->findOneBy(
            array('udi' => $udi)
        );

        $dql = "SELECT f FROM \App\Entity\File f
            WHERE f.id = :fileId
            AND f.fileset = :filesetId";

        $query = $this->entityManager->createQuery($dql);
        $query->setParameter('filesetId', $dataset->getDatasetSubmission()->getFileset());
        $query->setParameter('fileId', $fileId);
        /**  @var File $fileToDelete */
        $fileToDelete = $query->getResult()[0];
        try {
            $this->entityManager->remove($fileToDelete);
            $this->entityManager->flush();
            unlink($fileToDelete->getPhysicalFilePath());
        } catch (\Exception $e) {
            $io->error("Could not delete file." . $e->getMessage());
        }
        return 0;
    }
}