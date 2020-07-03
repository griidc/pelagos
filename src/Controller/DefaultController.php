<?php

namespace App\Controller;

use App\Entity\PersonResearchGroup;
use App\Entity\ResearchGroupRole;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Account;
use App\Entity\Dataset;
use App\Entity\DatasetSubmission;
use App\Entity\FundingCycle;
use App\Entity\ResearchGroup;
use App\Util\FundingOrgFilter;

/**
 * This is the default controller.
 */
class DefaultController extends AbstractController
{

    /**
     * The index action.
     *
     * @param FundingOrgFilter $fundingOrgFilter The funding organization filter utility.
     *
     * @Route("/", name="pelagos_nas_homepage", condition="'%custom_template%' matches '/nas-grp-base/'")
     *
     * @return Response A Response instance.
     */
    public function nasIndex(FundingOrgFilter $fundingOrgFilter)
    {
        $filter = array();
        if ($fundingOrgFilter->isActive()) {
            $filter = array('fundingOrganization' => $fundingOrgFilter->getFilterIdArray());
        }

        $fundingCycles = $this->get('doctrine')->getRepository(FundingCycle::class)->findBy($filter, array('name' => 'ASC'));

        $fundingCycleList = array();

        foreach ($fundingCycles as $fundingCycle) {
            $fundingCycleList[] = array(
                'id' => $fundingCycle->getId(),
                'name' => $fundingCycle->getName(),
                'researchGroups' => $this->getResearchGroupsArray($fundingCycle)
            );
        }

        return $this->render('Default/nas-grp-index.html.twig', array(
            'fundingCycles' => $fundingCycleList,
        ));
    }

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
                'shortName' => $researchGroup->getShortName(),
                'projectDirectors' => $this->getProjectDirectorList($researchGroup)
            );
        }
        return $researchGroups;
    }

    /**
     * Get the list of project directors in the research group.
     *
     * @param ResearchGroup $researchGroup An instance of Research group entity.
     *
     * @return array
     */
    private function getProjectDirectorList(ResearchGroup $researchGroup): array
    {
        $projectDirectors = array();

        foreach ($researchGroup->getPersonResearchGroups() as $personResearchGroup) {
            if ($personResearchGroup instanceof PersonResearchGroup
                and $personResearchGroup->getRole()->getName() === ResearchGroupRole::LEADERSHIP) {
                $projectDirectors[] = array(
                    'id' => $personResearchGroup->getPerson()->getId(),
                    'name' => $personResearchGroup->getPerson()->getFirstName()
                        . ' ' . $personResearchGroup->getPerson()->getLastName()
                );
            }
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
    public function index()
    {
        if ($this->getParameter('kernel.debug')) {
            return $this->render('Default/index.html.twig');
        } else {
            return $this->redirect('/', 302);
        }
    }

    /**
     * The admin action.
     *
     * @Route("/admin", name="pelagos_admin")
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
     * @Route("/sitemap.xml", name="pelagos_sitemap")
     *
     * @return StreamedResponse
     */
    public function showSiteMapXml()
    {
        $response = new StreamedResponse(function () {

            $datasets = $this->getDoctrine()->getRepository(Dataset::class)->findBy(
                array(
                    'availabilityStatus' =>
                    array(
                        DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE,
                        DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED,
                    )
                )
            );

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
