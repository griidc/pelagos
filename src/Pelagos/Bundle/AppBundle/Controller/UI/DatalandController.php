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

/**
 * The Dataset Monitoring controller.
 *
 * @Route("/data")
 */
class DatalandController extends UIController
{
    /**
     * The Dataland Page - dataset details per UDI.
     *
     * @param string $udi A UDI.
     *
     * @Route("/{udi}")
     *
     * @return Response A Response instance.
     */
    public function defaultAction($udi)
    {
        $datasets = $this->entityHandler->getBy('Pelagos:Dataset', array('udi' => $udi));
        
        if (count($datasets) != 1) {
            throw new \Exception("Got more than one return for UDI: $udi");
        }
        
        $dataset = $datasets[0];

        return $this->render(
            'PelagosAppBundle:Dataland:index.html.twig',
            array(
                'dataset' => $dataset,
                )
        );
    }
}
