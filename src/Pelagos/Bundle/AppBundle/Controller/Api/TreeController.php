<?php

namespace Pelagos\Bundle\AppBundle\Controller\Api;

use Doctrine\ORM\Query;

use Symfony\Component\HttpFoundation\Request;

use FOS\RestBundle\Controller\Annotations as Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\FundingOrganization;
use Pelagos\Entity\ResearchGroup;
use Pelagos\Entity\Person;

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
     * Gets the Funding Organization and Funding Cycle nodes.
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
     * @Rest\Get("/json/ra.json")
     *
     * @return string
     */
    public function getFundingOrganizationsAction(Request $request)
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
            $datasets = $this->get('doctrine.orm.entity_manager')
                ->getRepository(Dataset::class)
                ->filter(array(), $textFilter, $geoFilter);
            foreach ($datasets as $dataset) {
                $fundingOrganizations[$dataset[0]['researchGroup']['fundingCycle']['fundingOrganization']['id']] = true;
                $fundingCycles[$dataset[0]['researchGroup']['fundingCycle']['id']] = true;
            }
            $criteria['id'] = array_keys($fundingOrganizations);
        }
        return $this->render(
            'PelagosAppBundle:Api:Tree/research_awards.json.twig',
            array(
                'tree' => $tree,
                'fundingOrgs' => $this->container->get('doctrine.orm.entity_manager')
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
     * @param Request $request      The request object.
     * @param integer $fundingCycle The Funding Cycle to return Research Groups for.
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
     * @Rest\Get("/json/ra/projects/funding-cycle/{fundingCycle}.json")
     *
     * @return string
     */
    public function getResearchGroupsByFundingCycleAction(Request $request, $fundingCycle)
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
            $datasets = $this->get('doctrine.orm.entity_manager')
                ->getRepository(Dataset::class)
                ->filter(array(), $textFilter, $geoFilter);
            foreach ($datasets as $dataset) {
                $researchGroups[$dataset[0]['researchGroup']['id']] = true;
            }
            $criteria['id'] = array_keys($researchGroups);
        }
        return $this->render(
            'PelagosAppBundle:Api:Tree/projects.json.twig',
            array(
                'tree' => $tree,
                'projects' => $this->container->get('doctrine.orm.entity_manager')
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
     * @Rest\Get("/json/re.json")
     *
     * @return string
     */
    public function getLettersAction(Request $request)
    {
        $entityManager = $this->container->get('doctrine.orm.entity_manager');

        $entityManager
            ->getConfiguration()
            ->addCustomHydrationMode(
                'COLUMN_HYDRATOR',
                'Pelagos\DoctrineExtensions\Hydrators\ColumnHydrator'
            );
        $qb = $entityManager
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
            'PelagosAppBundle:Api:Tree/letters.json.twig',
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
     * @Rest\Get("/json/re/{letter}.json")
     *
     * @return string
     */
    public function getPeopleAction(Request $request, $letter)
    {
        $entityManager = $this->container->get('doctrine.orm.entity_manager');

        $qb = $entityManager
            ->getRepository(Person::class)
            ->createQueryBuilder('person');

        $firstLetterLastName = $qb->expr()->upper($qb->expr()->substring('person.lastName', 1, 1));

        $query = $qb
            ->select('person')
            ->where(
                $qb->expr()->like(
                    $qb->expr()->upper('person.lastName'),
                    $qb->expr()->upper(':letter')
                )
            )
            ->orderBy('person.lastName')
            ->orderBy('person.firstName')
            ->setParameter('letter', "$letter%")
            ->getQuery();
        $people = $query->getResult(Query::HYDRATE_ARRAY);

        return $this->render(
            'PelagosAppBundle:Api:Tree/researchers.json.twig',
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
     * @Rest\Get("/json/re/projects/peopleId/{personId}.json")
     *
     * @return string
     */
    public function getResearchGroupsByPersonAction(Request $request, $personId)
    {
        $person = $this->container->get('pelagos.entity.handler')->get(Person::class, $personId);

        $researchGroups = $person->getResearchGroups();

        usort($researchGroups, array(ResearchGroup::class, 'compareByName'));

        return $this->render(
            'PelagosAppBundle:Api:Tree/projects.json.twig',
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
