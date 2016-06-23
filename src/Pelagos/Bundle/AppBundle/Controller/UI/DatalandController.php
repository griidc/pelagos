<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\Metadata;

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
     * @throws NotFoundHttpException When no dataset is found with this UDI.
     * @throws \Exception            When more than one dataset is found with this UDI.
     *
     * @Route("/{udi}")
     *
     * @return Response A Response instance.
     */
    public function defaultAction($udi)
    {
        $datasets = $this->entityHandler->getBy('Pelagos:Dataset', array('udi' => $udi));

        if (count($datasets) == 0) {
            throw $this->createNotFoundException("No dataset found for UDI: $udi");
        }

        if (count($datasets) > 1) {
            throw new \Exception("Got more than one return for UDI: $udi");
        }

        $dataset = $datasets[0];
        $rawXml = null;

        if ($dataset->getMetadata() instanceof Metadata) {
            $rawXml = $dataset->getMetadata()->getXml()->asXml();
        }

        return $this->render(
            'PelagosAppBundle:Dataland:index.html.twig',
            $twigData = array(
                'dataset' => $dataset,
                'rawxml' => $rawXml,
            )
        );
    }
}
