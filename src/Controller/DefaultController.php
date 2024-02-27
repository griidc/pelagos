<?php

namespace App\Controller;

use App\Controller\UI\StatsController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\FundingCycle;
use App\Entity\PersonResearchGroup;
use App\Util\FundingOrgFilter;
use Symfony\Component\HttpFoundation\Request;

/**
 * This is the default controller.
 */
class DefaultController extends AbstractController
{
    /**
     * Get research groups array in the funding cycle.
     *
     * @param FundingCycle $fundingCycle An instance of Funding cycle entity.
     *
     * @return array
     */
    private function getResearchGroupsArray(FundingCycle $fundingCycle): array
    {
        $researchGroups = array();

        foreach ($fundingCycle->getResearchGroups() as $researchGroup) {
            $researchGroups[] = array(
                'id' => $researchGroup->getId(),
                'name' => $researchGroup->getName(),
            );
        }
        return $researchGroups;
    }

    /**
     * Get the list of project directors.
     *
     * @param array $leadershipPersons Collection of Persons with Leadership role.
     *
     * @return array
     */
    private function getProjectDirectorList(array $leadershipPersons): array
    {
        $projectDirectors = array();

        foreach ($leadershipPersons as $leadershipPerson) {
            $projectDirectors[] = array(
                'id' => $leadershipPerson->getPerson()->getId(),
                'name' => $leadershipPerson->getPerson()->getLastName()
                    . ', ' . $leadershipPerson->getPerson()->getFirstName()
                    . ' - ' . $leadershipPerson->getResearchGroup()->getFundingCycle()->getName(),
                'researchGroupId' => $leadershipPerson->getResearchGroup()->getId()
            );
        }

        return $projectDirectors;
    }

    /**
     * The index action.
     *
     * @Route("/", name="pelagos_homepage")
     *
     * @return Response A Response instance.
     */
    public function index(FundingOrgFilter $fundingOrgFilter, StatsController $statsController, Request $request)
    {
        $customTemplate = $this->getParameter('custom_template');

        if ($customTemplate) {
            $customTemplate = str_replace("base", "index", $customTemplate);
            $filter = array();
            if ($fundingOrgFilter->isActive()) {
                $filter = array('fundingOrganization' => $fundingOrgFilter->getFilterIdArray());
            }

            $fundingCycles = $this->get('doctrine')->getRepository(FundingCycle::class)->findBy($filter, array('name' => 'ASC'));

            $leadershipPersons = $this->get('doctrine')->getRepository(PersonResearchGroup::class)->getLeadershipPeople();

            $projectDirectorList = $this->getProjectDirectorList($leadershipPersons);

            $fundingCycleList = array();

            foreach ($fundingCycles as $fundingCycle) {
                $fundingCycleList[] = array(
                    'id' => $fundingCycle->getId(),
                    'name' => $fundingCycle->getName(),
                    'researchGroups' => $this->getResearchGroupsArray($fundingCycle)
                );
            }
            $stats = json_decode($statsController->getStatisticsJson(new Request())->getContent(), true);

            return $this->render("Default/$customTemplate", array(
                'fundingCycles' => $fundingCycleList,
                'projectDirectors' => $projectDirectorList,
                'stats' => [
                    'datasetCount' => $stats['totalDatasets'],
                    'datasetsTotalFileSize' => preg_replace('/[^0-9], ./', "", $stats['totalSize']),
                    'researchGroupCount' => $stats['researchGroupCount'],
                    'peopleCount' => $stats['peopleCount'],
                ],
            ));
        }

        $mainsite = $this->getParameter('main_site');
        $mainsite = is_string($mainsite) ? $mainsite : '/';

        if ($request->getHost() == "data.griidc.org") {
            return $this->redirect($mainsite, 302);
        } else {
            return $this->render('Default/index.html.twig');
        }
    }

    /**
     * The admin action.
     *
     * @Route("/pelagos-admin", name="pelagos_admin")
     *
     * @return Response
     */
    public function admin()
    {
        return $this->render('Default/admin.html.twig');
    }

    /**
     * Get the sitemap.xml containing all dataset urls.
     *
     * @param EntityManagerInterface $entityManager    The Doctrine Entity Manager.
     * @param FundingOrgFilter       $fundingOrgFilter The funding organization filter utility.
     *
     * @Route("/sitemap.xml", name="pelagos_sitemap")
     *
     * @return StreamedResponse
     */
    public function showSiteMapXml(EntityManagerInterface $entityManager, FundingOrgFilter $fundingOrgFilter)
    {
        $criteria = array(
            'availabilityStatus' =>
            array(
                DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE,
                DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED,
            )
        );

        if ($fundingOrgFilter->isActive()) {
            $criteria = array_merge($criteria, array(
                'researchGroup' =>
                $fundingOrgFilter->getResearchGroupsIdArray()
            ));
        }

        $datasets = $entityManager->getRepository(Dataset::class)->findBy($criteria);

        $response = new StreamedResponse(function () use ($datasets) {
            echo $this->renderView(
                'Default/sitemap.xml.twig',
                array(
                    'datasets' => $datasets
                )
            );
        });

        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }
}
