<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Pelagos\Entity\FundingOrganization;
use Pelagos\Entity\FundingCycle;
use Pelagos\Entity\ResearchGroup;
use Pelagos\Entity\Person;

/**
 * The Dataset Monitoring controller.
 *
 * @Route("/dataset-monitoring")
 */
class DatasetMonitoringController extends UIController
{
    /**
     * The default action.
     *
     * @Route("")
     * @Method("GET")
     *
     * @return Response A Symfony Response instance.
     */
    public function defaultAction()
    {
        return $this->render('PelagosAppBundle:DatasetMonitoring:index.html.twig');
    }

    /**
     * The Dataset Monitoring display all research groups of a Funding Cycle.
     *
     * @param string $fc       A Pelagos Funding Cycle entity id.
     * @param string $renderer Either 'browser' or 'html2pdf'.
     *
     * @Route("/dataset-monitoring/research-groups/{fc}/{renderer}", defaults={"renderer" = "browser"})
     *
     * @return Response A Response instance.
     */
    public function allResearchGroupAction($fc, $renderer)
    {
        $fundingCycle = $this->entityHandler->get('Pelagos:FundingCycle', $fc);
        $title = $fundingCycle->getName();
        $researchGroups = $fundingCycle->getResearchGroups();

        if ('html2pdf' == $renderer) {
            return $this->render(
                'PelagosAppBundle:DatasetMonitoring:pdf.html',
                array(
                    'researchGroups' => array($researchGroup),
                    'header' => $title,
                    'pdfFileName' => "Dataset Monitoring - $title"
                )
            );
        } else {
            return $this->render(
                'PelagosAppBundle:DatasetMonitoring:projects.html',
                array(
                    'researchGroups' => array($researchGroup),
                    'header' => $title,
                    'pdfFileName' => "Dataset Monitoring - $title"
                )
            );
        }
    }

    /**
     * The Dataset Monitoring display by research group.
     *
     * @param string $rg       A Pelagos Research Group entity id.
     * @param string $renderer Either 'browser' or 'html2pdf'.
     *
     * @Route("/dataset-monitoring/research-group/{rg}/{renderer}", defaults={"renderer" = "browser"})
     *
     * @return Response A Response instance.
     */
    public function researchGroupAction($rg, $renderer)
    {
        $researchGroup = $this->entityHandler->get('Pelagos:ResearchGroup', $rg);
        $title = $researchGroup->getTitle();
        if ('html2pdf' == $renderer) {
            return $this->render(
                'PelagosAppBundle:DatasetMonitoring:pdf.html',
                array(
                    'researchGroups' => array($researchGroup),
                    'header' => $title,
                    'pdfFilename' => "Dataset Monitoring - $title"
                )
            );
        } else {
            return $this->render(
                'PelagosAppBundle:DatasetMonitoring:projects.html',
                array(
                    'researchGroups' => array($researchGroup),
                    'header' => $title,
                    'pdfFilename' => "Dataset Monitoring - $title"
                )
            );
        }
    }

    /**
     * The Dataset Monitoring display by a researcher.
     *
     * @param string $id       A Pelagos Person entity id of a researcher.
     * @param string $renderer Either 'browser' or 'html2pdf'.
     *
     * @Route("/dataset-monitoring/researcher/{id}/{renderer}", defaults={"renderer" = "browser"})
     *
     * @return Response A Response instance.
     */
    public function researcherAction($id, $renderer)
    {
        $researcher = $this->entityHandler->get('Pelagos:Person', $id);
        $title = $researcher->getLastName() . ', ' . $researcher->getFirstName();
        $researchGroups = $researcher->getResearchGroups();
        if ('html2pdf' == $renderer) {
            return $this->render(
                'PelagosAppBundle:DatasetMonitoring:pdf.html',
                array(
                    'researchGroups' => $researchGroups,
                    'header' => $title,
                    'pdfFilename' => 'Dataset Monitoring - ' . $researcher->getLastName() . ', ' . $researcher->getFirstName()
                )
            );
        } else {
            return $this->render(
                'PelagosAppBundle:DatasetMonitoring:projects.html',
                array(
                    'researchGroups' => $researchGroups,
                    'header' => $title,
                    'pdfFilename' => 'Dataset Monitoring - ' . $researcher->getLastName() . ', ' . $researcher->getFirstName()
                )
            );
        }
    }
}
