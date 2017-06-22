<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;
use Pelagos\Entity\DIF;
use Pelagos\Entity\FundingOrganization;
use Pelagos\Entity\FundingCycle;
use Pelagos\Entity\ResearchGroup;
use Pelagos\Entity\Person;

use Pelagos\Util\ISOMetadataExtractorUtil;

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
     * @param string $id       A Pelagos Funding Cycle entity id.
     * @param string $renderer Either 'browser' or 'html2pdf'.
     *
     * @Route("/funding-cycle/{id}/{renderer}", defaults={"renderer" = "browser"})
     *
     * @return Response A Response instance.
     */
    public function allResearchGroupAction($id, $renderer)
    {
        $fundingCycle = $this->entityHandler->get('Pelagos:FundingCycle', $id);
        $title = $fundingCycle->getName();
        $pdfFilename = 'Dataset Monitoring-' . date('Y-m-d');
        $researchGroups = $fundingCycle->getResearchGroups();

        if ('html2pdf' == $renderer) {
            return $this->render(
                'PelagosAppBundle:DatasetMonitoring:pdf.html.twig',
                array(
                    'researchGroups' => $researchGroups,
                    'header' => $title,
                    'pdfFilename' => $pdfFilename,
                )
            );
        } else {
            return $this->render(
                'PelagosAppBundle:DatasetMonitoring:projects.html.twig',
                array(
                    'researchGroups' => $researchGroups,
                    'header' => $title,
                    'pdfFilename' => $pdfFilename,
                    'id' => $id,
                )
            );
        }
    }

    /**
     * The Dataset Monitoring display by research group.
     *
     * @param string $id       A Pelagos Research Group entity id.
     * @param string $renderer Either 'browser' or 'html2pdf'.
     *
     * @Route("/research-group/{id}/{renderer}", defaults={"renderer" = "browser"})
     *
     * @return Response A Response instance.
     */
    public function researchGroupAction($id, $renderer)
    {
        $researchGroup = $this->entityHandler->get('Pelagos:ResearchGroup', $id);
        $title = $researchGroup->getName();
        $pdfFilename = 'Dataset Monitoring-' . date('Y-m-d');
        if ('html2pdf' == $renderer) {
            return $this->render(
                'PelagosAppBundle:DatasetMonitoring:pdf.html.twig',
                array(
                    'researchGroups' => array($researchGroup),
                    'header' => $title,
                    'pdfFilename' => $pdfFilename,
                )
            );
        } else {
            return $this->render(
                'PelagosAppBundle:DatasetMonitoring:projects.html.twig',
                array(
                    'researchGroups' => array($researchGroup),
                    'header' => $title,
                    'pdfFilename' => $pdfFilename,
                    'id' => $id,
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
     * @Route("/researcher/{id}/{renderer}", defaults={"renderer" = "browser"})
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
                'PelagosAppBundle:DatasetMonitoring:pdf.html.twig',
                array(
                    'researchGroups' => $researchGroups,
                    'header' => $title,
                    'pdfFilename' => 'Dataset Monitoring - ' . $researcher->getLastName() . ' ' . $researcher->getFirstName()
                )
            );
        } else {
            return $this->render(
                'PelagosAppBundle:DatasetMonitoring:projects.html.twig',
                array(
                    'researchGroups' => $researchGroups,
                    'header' => $title,
                    'pdfFilename' => 'Dataset Monitoring - ' . $researcher->getLastName() . ' ' . $researcher->getFirstName(),
                    'id' => $id,
                )
            );
        }
    }

    /**
     * The Dataset Monitoring details per UDI.
     *
     * @param string $udi A UDI.
     *
     * @Route("/dataset_details/{udi}")
     *
     * @return Response A Response instance.
     */
    public function datasetDetailsAction($udi)
    {
        $datasets = $this->entityHandler->getBy('Pelagos:Dataset', array('udi' => $udi));

        $dataset = $datasets[0];

        $datasetSubmission = $dataset->getDatasetSubmission();

        // If we have approved Metadata, load contact into datasetSubmission.
        if ($datasetSubmission instanceof DatasetSubmission
            and $datasetSubmission->getMetadataStatus() === DatasetSubmission::METADATA_STATUS_ACCEPTED
        ) {
            ISOMetadataExtractorUtil::populateDatasetSubmissionWithXMLValues(
                $dataset->getMetadata()->getXml(),
                $datasetSubmission,
                $this->getDoctrine()->getManager()
            );
        }

        return $this->render(
            'PelagosAppBundle:DatasetMonitoring:dataset_details.html.twig',
            array(
                'datasets' => $datasets,
                )
        );
    }
}
