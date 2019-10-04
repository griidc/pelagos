<?php

namespace App\Controller\Api;

use Doctrine\ORM\Query;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use FOS\RestBundle\Controller\Annotations\View;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use App\Handler\EntityHandler;
use App\Entity\FundingOrganization;
use App\Entity\ResearchGroup;
use App\Entity\Person;

use App\Util\DatasetIndex;

/**
 * The Tree API controller.
 */
class TreeController extends EntityController
{
    /**
     * The default tree config.
     *
     * @var array
     */
    protected $defaultTree = array(
        'rfp_color' => 'black',
        'rfp_action' => '',
        'project_color' => 'black',
        'project_action' => '',
        'researcher_color' => 'black',
        'researcher_action' => '',
        'max_depth' => 3,
        'expand_to_depth' => 0,
    );

    /**
     * @var EntityManager
     */
    protected $doctrineOrmEntityManager;

    /**
     * @var EntityHandler
     */
    protected $entityHandler;

    /**
     * @var FormFactoryInterface Form factory instance.
     */
    protected $formFactory;

    /**
     * TreeController constructor.
     *
     * @param EntityManagerInterface $doctrineOrmEntityManager
     * @param EntityHandler $entityHandler
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(EntityManagerInterface $doctrineOrmEntityManager, EntityHandler $entityHandler, FormFactoryInterface $formFactory)
    {
        $this->entityHandler = $entityHandler;
        $this->formFactory = $formFactory;
        parent::__construct($entityHandler, $formFactory);
        $this->doctrineOrmEntityManager = $doctrineOrmEntityManager;
    }


    /**
     * Gets the Funding Organization and Funding Cycle nodes.
     *
     * @param Request      $request      The request object.
     * @param DatasetIndex $datasetIndex Dataset index object.
     *
     * @return string
     * @ApiDoc(
     *   section = "Tree",
     *   parameters = {
     *     {"name"="tree", "dataType"="string", "required"=false, "description"="The tree configuration"}
     *   },
     *   statusCodes = {
     *     200 = "The requested Funding Organization nodes were successfully retrieved.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Route(
     *     "/api/tree/json/ra.json",
     *     name="pelagos_api_tree_get_funding_organizations",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     */
    public function getFundingOrganizationsAction(Request $request, DatasetIndex $datasetIndex)
    {
        $tree = $this->buildTreeConfig($request);
        $filter = false;
        $textFilter = null;
        if (array_key_exists('filter', $tree) and !empty($tree['filter'])) {
            $textFilter = $tree['filter'];
            $filter = true;
        }
        $geoFilter = null;
        if (array_key_exists('geo_filter', $tree) and !empty($tree['geo_filter'])) {
            $geoFilter = $tree['geo_filter'];
            $filter = true;
        }
        $criteria = array();
        $fundingCycles = array();
        if ($filter) {
            $fundingOrganizations = array();
            $datasets = $datasetIndex->search(array(), $textFilter, $geoFilter);
            foreach ($datasets as $dataset) {
                $fundingOrganizations[$dataset->researchGroup['fundingCycle']['fundingOrganization']['id']] = true;
                $fundingCycles[$dataset->researchGroup['fundingCycle']['id']] = true;
            }
            $criteria['id'] = array_keys($fundingOrganizations);
        }
        return $this->render(
            'Api/Tree/research_awards.json.twig',
            array(
                'tree' => $tree,
                'fundingOrgs' => $this->doctrineOrmEntityManager
                    ->getRepository(FundingOrganization::class)
                    ->findBy(
                        $criteria,
                        array('sortOrder' => 'ASC', 'name' => 'ASC')
                    ),
                'fundingCycleIds' => array_keys($fundingCycles),
            )
        );
    }

    /**
     * Gets the Research Group nodes for a Funding Cycle.
     *
     * @param Request $request The request object.
     * @param integer $fundingCycle The Funding Cycle to return Research Groups for.
     * @param DatasetIndex $datasetIndex
     *
     * @return string
     * @ApiDoc(
     *   section = "Tree",
     *   parameters = {
     *     {"name"="tree", "dataType"="string", "required"=false, "description"="The tree configuration"}
     *   },
     *   statusCodes = {
     *     200 = "The requested Research Group nodes were successfully retrieved.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Route(
     *     "/api/tree/json/ra/projects/funding-cycle/{fundingCycle}.json",
     *     name="pelagos_api_tree_get_research_groups_by_funding_cycle",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     */
    public function getResearchGroupsByFundingCycleAction(Request $request, $fundingCycle, DatasetIndex $datasetIndex)
    {
        $tree = $this->buildTreeConfig($request);
        $filter = false;
        $textFilter = null;
        if (array_key_exists('filter', $tree) and !empty($tree['filter'])) {
            $textFilter = $tree['filter'];
            $filter = true;
        }
        $geoFilter = null;
        if (array_key_exists('geo_filter', $tree) and !empty($tree['geo_filter'])) {
            $geoFilter = $tree['geo_filter'];
            $filter = true;
        }
        $criteria = array('fundingCycle' => $fundingCycle);
        if ($filter) {
            $researchGroups = array();
            $datasets = $datasetIndex->search(array(), $textFilter, $geoFilter);
            foreach ($datasets as $dataset) {
                $researchGroups[$dataset->researchGroup['id']] = true;
            }
            $criteria['id'] = array_keys($researchGroups);
        }
        return $this->render(
            'Api/Tree/projects.json.twig',
            array(
                'tree' => $tree,
                'projects' => $this->doctrineOrmEntityManager
                    ->getRepository(ResearchGroup::class)
                    ->findBy(
                        $criteria,
                        array('name' => 'ASC')
                    ),
            )
        );
    }

    /**
     * Gets the Researcher letter nodes.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Tree",
     *   parameters = {
     *     {"name"="tree", "dataType"="string", "required"=false, "description"="The tree configuration"}
     *   },
     *   statusCodes = {
     *     200 = "The requested Funding Organization nodes were successfully retrieved.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Route(
     *     "/api/tree/json/re.json",
     *     name="pelagos_api_tree_get_letters",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return string
     */
    public function getLettersAction(Request $request)
    {
        $this->doctrineOrmEntityManager
            ->getConfiguration()
            ->addCustomHydrationMode(
                'COLUMN_HYDRATOR',
                'App\DoctrineExtensions\Hydrators\ColumnHydrator'
            );
        $qb = $this->doctrineOrmEntityManager
            ->getRepository(Person::class)
            ->createQueryBuilder('person');

        $firstLetterLastName = $qb->expr()->upper($qb->expr()->substring('person.lastName', 1, 1));

        $query = $qb
            ->select($firstLetterLastName)
            ->distinct()
            ->orderBy($firstLetterLastName)
            ->getQuery();
        $letters = $query->getResult('COLUMN_HYDRATOR');

        return $this->render(
            'Api/Tree/letters.json.twig',
            array(
                'tree' => $this->buildTreeConfig($request),
                'letters' => $letters,
            )
        );
    }

    /**
     * Gets the Researcher nodes whose last name starts with a letter.
     *
     * @param Request $request The request object.
     * @param string  $letter  The letter for which to return Researchers whose last name starts with.
     *
     * @ApiDoc(
     *   section = "Tree",
     *   parameters = {
     *     {"name"="tree", "dataType"="string", "required"=false, "description"="The tree configuration"}
     *   },
     *   statusCodes = {
     *     200 = "The requested Funding Organization nodes were successfully retrieved.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Route(
     *     "/api/tree/json/re/{letter}.json",
     *     name="pelagos_api_tree_get_people",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return string
     */
    public function getPeopleAction(Request $request, $letter)
    {
        $qb = $this->doctrineOrmEntityManager
            ->getRepository(Person::class)
            ->createQueryBuilder('person');

        $query = $qb
            ->select('person')
            ->where(
                $qb->expr()->eq(
                    // First letter of Last Name.
                    $qb->expr()->upper($qb->expr()->substring('person.lastName', 1, 1)),
                    $qb->expr()->upper(':letter')
                )
            )
            ->orderBy('person.lastName')
            ->addOrderBy('person.firstName')
            ->setParameter('letter', "$letter")
            ->getQuery();
        $people = $query->getResult(Query::HYDRATE_ARRAY);

        return $this->render(
            'Api/Tree/researchers.json.twig',
            array(
                'tree' => $this->buildTreeConfig($request),
                'people' => $people,
            )
        );
    }

    /**
     * Gets the Research Group nodes for a person.
     *
     * @param Request $request  The request object.
     * @param integer $personId The id of the Person to return Research Groups for.
     *
     * @ApiDoc(
     *   section = "Tree",
     *   parameters = {
     *     {"name"="tree", "dataType"="string", "required"=false, "description"="The tree configuration"}
     *   },
     *   statusCodes = {
     *     200 = "The requested Research Group nodes were successfully retrieved.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Route(
     *     "/api/tree/json/re/projects/peopleId/{personId}.json",
     *     name="pelagos_api_tree_get_research_groups_by_person",
     *     methods={"GET"},
     *     defaults={"_format"="json"}
     *     )
     *
     * @return string
     */
    public function getResearchGroupsByPersonAction(Request $request, $personId)
    {
        $person = $this->entityHandler->get(Person::class, $personId);

        $researchGroups = $person->getResearchGroups();

        usort($researchGroups, array(ResearchGroup::class, 'compareByName'));

        return $this->render(
            'Api/Tree/projects.json.twig',
            array(
                'tree' => $this->buildTreeConfig($request),
                'projects' => $researchGroups,
            )
        );
    }

    /**
     * Build the tree configuration array from the default tree and a Symfony request.
     *
     * @param Request $request A Symfony request object.
     *
     * @return array
     */
    protected function buildTreeConfig(Request $request)
    {
        $tree = $this->defaultTree;

        if (null !== $request->query->get('tree')) {
            $tree = array_merge(
                $tree,
                json_decode(urldecode($request->query->get('tree')), true)
            );
        }

        return $tree;
    }
}
