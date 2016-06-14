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
     * The Dataset Monitoring display all funding organizations.
     *
     * @param string $renderer Either 'browser' or 'html2pdf'.
     *
     * @Route("/dataset-monitoring/funding-organizations/{renderer}", defaults={"renderer" = "browser"})
     *
     * @return Response A Response instance.
     */
    public function allFundingOrganizationAction($renderer)
    {
        $allFundingOrganizations = $this->entityHandler->getAll(FundingOrganization::class);
        if ('html2pdf' == $renderer) {
            return $this->render(
                'PelagosAppBundle:DatasetMonitoring:fundingOrganization.html2pdf.html.twig',
                $allFundingOrganizations
            );
        } else {
            return $this->render(
                'PelagosAppBundle:DatasetMonitoring:fundingOrganization.html.twig',
                $allFundingOrganizations
            );
        }
    }

    /**
     * The Dataset Monitoring display a funding organization.
     *
     * @param string $fo       A Pelagos Funding Organization entity id.
     * @param string $renderer Either 'browser' or 'html2pdf'.
     *
     * @Route("/dataset-monitoring/funding-organization/{fo}/{renderer}", defaults={"renderer" = "browser"})
     *
     * @return Response A Response instance.
     */
    public function fundingOrganizationAction($fo, $renderer)
    {
        $fundingOrganization = $this->entityHandler->get('Pelagos:FundingOrganization', $fo);
        if ('html2pdf' == $renderer) {
            return $this->render(
                'PelagosAppBundle:DatasetMonitoring:fundingOrganization.html2pdf.html.twig',
                array($fundingOrganization)
            );
        } else {
            return $this->render(
                'PelagosAppBundle:DatasetMonitoring:fundingOrganization.html.twig',
                array($fundingOrganization)
            );
        }
    }

    /**
     * The Dataset Monitoring display all funding cycles in a Funding Organization.
     *
     * @param string $fo       A Pelagos Funding Organization entity id.
     * @param string $renderer Either 'browser' or 'html2pdf'.
     *
     * @Route("/dataset-monitoring/funding-cycle/{fo}/{renderer}", defaults={"renderer" = "browser"})
     *
     * @return Response A Response instance.
     */
    public function allFundingCycleAction($fo, $renderer)
    {
        $fundingCycles = $this->entityHandler->get('Pelagos:FundingOrganization', $fo)->getFundingcycles();
        if ('html2pdf' == $renderer) {
            return $this->render(
                'PelagosAppBundle:DatasetMonitoring:fundingCycle.html2pdf.html.twig',
                $fundingCycles
            );
        } else {
            return $this->render(
                'PelagosAppBundle:DatasetMonitoring:fundingCycle.html.twig',
                $fundingCycles
            );
        }
    }

    /**
     * The Dataset Monitoring display by funding cycle.
     *
     * @param string $fc       A Pelagos Funding Cycle entity id.
     * @param string $renderer Either 'browser' or 'html2pdf'.
     *
     * @Route("/dataset-monitoring/funding-cycle/{fc}/{renderer}", defaults={"renderer" = "browser"})
     *
     * @return Response A Response instance.
     */
    public function fundingCycleAction($fc, $renderer)
    {
        $fundingCycle = $this->entityHandler->get('Pelagos:FundingCycle', $fc);
        if ('html2pdf' == $renderer) {
            return $this->render(
                'PelagosAppBundle:DatasetMonitoring:fundingCycle.html2pdf.html.twig',
                $fundingCycle
            );
        } else {
            return $this->render(
                'PelagosAppBundle:DatasetMonitoring:fundingCycle.html.twig',
                $fundingCycle
            );
        }
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
        $researchGroups = $this->entityHandler->get('Pelagos:ResearchGroup', $fc)->getResearchGroups();
        if ('html2pdf' == $renderer) {
            return $this->render(
                'PelagosAppBundle:DatasetMonitoring:researchGroup.html2pdf.html.twig',
                $researchGroups
            );
        } else {
            return $this->render(
                'PelagosAppBundle:DatasetMonitoring:researchGroup.html.twig',
                $researchGroups
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
                'PelagosAppBundle:DatasetMonitoring:researchGroup.html2pdf.html.twig',
                array('data' => array($researchGroup), 'header' => $title)
            );
        } else {
            return $this->render(
                'PelagosAppBundle:DatasetMonitoring:researchGroup.html.twig',
                array('data' => array($researchGroup), 'header' => $title)
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
        if ('html2pdf' == $renderer) {
            return $this->render(
                'PelagosAppBundle:DatasetMonitoring:researcher.html2pdf.html.twig',
                $researcher
            );
        } else {
            return $this->render(
                'PelagosAppBundle:DatasetMonitoring:researcher.html.twig',
                $researcher
            );
        }
    }
}
