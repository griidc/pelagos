<?php

namespace App\Command;

use App\Entity\Keyword;
use App\Enum\KeywordType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\ORM\EntityManagerInterface;
use EasyRdf\Graph;
use EasyRdf\Literal;
use EasyRdf\Resource;
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
            ->addArgument('action', InputArgument::REQUIRED, 'Action to use. [IMPORT|SORT|EXPAND]')
            ->addArgument('type', InputArgument::REQUIRED, 'The type of data to import.')
            ->addArgument('dataURI', InputArgument::OPTIONAL, 'The file, or URI with the data.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $action = $input->getArgument('action');
        $type = $input->getArgument('type');
        $dataURI = $input->getArgument('dataURI');

        try {
            switch (strtoupper($action)) {
                case 'IMPORT':
                    $type = KeywordType::tryFrom($type);
            $this->importKeyword($type, $dataURI);
                    break;
                case 'SORT':
                    $type = KeywordType::tryFrom($type);
            $this->sortKeyword($type, $io);
                    break;
                case 'EXPAND':
                    $this->expandKeyword($type, $io);
                    break;
                default:
                    throw new \Exception('No valid action given!');
                    break;
            }
        } catch (\Exception $e) {
            $io->caution($e->getMessage());
            return Command::FAILURE;
        }

        $this->entityManager->flush();

        $io->success('DONE!');

        return Command::SUCCESS;
    }

    private function expandKeyword($identifier, $io)
    {
        $keywordRepository = $this->entityManager->getRepository(Keyword::class);
        $keyword = $keywordRepository->findOneBy(['identifier' => $identifier]);
        dump($keyword);
        if ($keyword instanceof Keyword) {
            $expanded = $keyword->isExpanded();

            $io->choice('Do you want this node expanded?', ['Yes', 'No'], $expanded ? 'Yes' : 'No');
        } else {
            throw new \Exception('Keyword not found!');
        }


    }

    private function sortKeyword(KeywordType $keywordType, SymfonyStyle $io): void
    {
        $io->note('Sorting, ITS SLOW');
        $keywordRepository = $this->entityManager->getRepository(Keyword::class);
        $keywords = $keywordRepository->findBy(['type' => $keywordType->value]);
        $keywordCollection = new ArrayCollection($keywords);

        $io->progressStart($keywordCollection->count());

        foreach ($keywords as $keyword) {
            $parentUri = $keyword->getParentUri();

            if (!empty($parentUri)) {
                $path = $this->getParentPath($keywordCollection, $parentUri, ' > ' . $keyword->getLabel());
            } else {
                $path = $keyword->getLabel();
            }

            $keyword->setDisplayPath($path);
            $keywordRepository->save($keyword);

            $io->progressAdvance();
        }

        $io->progressFinish();
    }

    private function getParentPath(ArrayCollection $keywordCollection, string $parentUri, string $path = ''): string
    {
        $criteria = Criteria::create()
            ->where(
                new Comparison('referenceUri', '=', $parentUri)
            );

        $parentKeyword = $keywordCollection->matching($criteria);

        if (0 != $parentKeyword->count()) {
            $keyword = $parentKeyword->first();
            $path = $keyword->getLabel() . $path;
            $parentUri = $keyword->getParentUri();
            if (!empty($parentUri)) {
                $path = $this->getParentPath($keywordCollection, $parentUri) . ' > ' . $path;
            }
        }

        return $path;
    }

    private function importKeyword(KeywordType $keywordType, string $dataURI): void
    {
        $keywordReposity = $this->entityManager->getRepository(Keyword::class);

        if (KeywordType::TYPE_GCMD === $keywordType) { // gcmd
            // https://gcmd.earthdata.nasa.gov/kms/concepts/concept_scheme/discipline/?format=rdf (DISCIPLINE)
            // https://gcmd.earthdata.nasa.gov/kms/concepts/concept_scheme/sciencekeywords/?format=rdf&page_num=2 (KEYWORDS)

            $keywords = new Graph($dataURI);
            $keywords->load();
            $resources = $keywords->allOfType('skos:Concept');

            foreach ($resources as $resource) {
                $uri = $resource->getUri();
                $label = $this->getPropertyValue($resource, 'skos:prefLabel');
                $broader = $this->getPropertyValue($resource, 'skos:broader');
                $definition = $this->getPropertyValue($resource, 'skos:definition');
                $identifier = $resource->localName();

                $keyword = new Keyword();
                $keyword->setType(KeywordType::TYPE_GCMD);
                $keyword->setIdentifier($identifier);
                $keyword->setLabel($label);
                $keyword->setReferenceUri($uri);
                $keyword->setParentUri($broader);
                $keyword->setDefinition($definition);
                $keywordReposity->save($keyword);
            }
        } elseif (KeywordType::TYPE_ANZSRC === $keywordType) { // anzsrc
            // https://vocabs.ardc.edu.au/repository/api/lda/anzsrc-2020-for/concept.json
            $fileData = file_get_contents($dataURI);

            $json = json_decode($fileData);

            $items = $json->result->items;

            foreach ($items as $item) {
                $uri = $item->_about;
                $label = $item->prefLabel->_value;
                $broader = property_exists($item, 'broader') ? $item->broader : null;
                $definition = property_exists($item, 'definition') ? $item->definition : null;
                $identifier = $item->notation;

                $keyword = new Keyword();
                $keyword->setType(KeywordType::TYPE_ANZSRC);
                $keyword->setIdentifier($identifier);
                $keyword->setLabel($label);
                $keyword->setReferenceUri($uri);
                $keyword->setParentUri($broader);
                $keyword->setDefinition($definition);
                $keywordReposity->save($keyword);
            }
        }
    }

    private function getPropertyValue(Resource $resource, string $propertyName): mixed
    {
        $property = $resource->get($propertyName);

        if ($property instanceof Literal) {
            return $property->getValue();
        }

        return $property;
    }
}
