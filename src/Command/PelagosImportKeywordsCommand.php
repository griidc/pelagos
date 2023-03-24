<?php

namespace App\Command;

use App\Entity\Keyword;
use App\Enum\KeywordType;
use App\Repository\KeywordRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'pelagos:import-keywords',
    description: 'Add a short description for your command',
)]
class PelagosImportKeywordsCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('dataURI', InputArgument::REQUIRED, 'The file, or URI with the data.')
            ->addArgument('type', InputArgument::REQUIRED, 'The type of data to import.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dataURI = $input->getArgument('dataURI');
        $type = $input->getArgument('type');

        $fileData = file_get_contents($dataURI);

        $json = json_decode($fileData);

        $items = $json->result->items;

        $prefix = "https://vocabs.ardc.edu.au/repository/api/lda/anzsrc-2020-for/resource.json?uri=";

        $keywordReposity = $this->entityManager->getRepository(Keyword::class);

        foreach ($items as $item) {
            $aboutURI = $item->_about;
            $conceptData = file_get_contents($prefix . $aboutURI);
            $conceptJson = json_decode($conceptData);
            $keyword = new Keyword();
            $keyword->setType(KeywordType::TYPE_ANZSRC);
            $keyword->setJson($conceptJson);
            $keywordReposity->save($keyword);
        }

        $this->entityManager->flush();

        $io->success('DONE!');

        return Command::SUCCESS;
    }
}
