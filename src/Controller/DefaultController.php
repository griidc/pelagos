<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\ResearchGroup;
use App\Util\FundingOrgFilter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\DatasetSubmission;
use App\Entity\Dataset;

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
        $researchGroups = array();

        if ($fundingOrgFilter->isActive()) {
            $researchGroups = $this->get('doctrine')->getRepository(ResearchGroup::class)->findBy(array('id' => $fundingOrgFilter->getResearchGroupsIdArray() ));
        }

        return $this->render('Default/nas-grp-index.html.twig', array(
            'researchGroups' => $researchGroups,
        ));
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
