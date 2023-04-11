<?php

namespace App\Command;

use App\Entity\Keyword;
use App\Enum\KeywordType;
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
            ->addArgument('dataURI', InputArgument::REQUIRED, 'The file, or URI with the data.')
            ->addArgument('type', InputArgument::OPTIONAL, 'The type of data to import.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dataURI = $input->getArgument('dataURI');
        $type = KeywordType::fromString($input->getArgument('type'));
        $keywordReposity = $this->entityManager->getRepository(Keyword::class);

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

        $this->entityManager->flush();

        $io->success('DONE!');

        return Command::SUCCESS;
    }

    private function getPropertyValue(Resource $resource, string $propertyName): mixed
    {
        $property = $resource->get($propertyName);

        if ($property instanceof Literal) {
            return $property->getValue();
        }

        return  $property;
    }
}
