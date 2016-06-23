<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\Publication;
use Pelagos\Util\PubLinkUtil;

/**
 * The PublicationDatasetLink controller.
 *
 * @Route("/publink")
 */
class PublicationDatasetLinkController extends UIController
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
        return $this->render('PelagosAppBundle:PublicationDatasetLink:index.html.twig');
    }

    /**
     * Generate citations for publications.
     *
     * @Route("/services/citation/publication/{doi}", requirements={"doi"=".+"})
     * @Method("GET")
     *
     * @param string $doi A string containing a DOI
     *
     * @return array An array of Citation and status elements.
     */
    public function getCitationPublicationAction($doi)
    {
        $helper = new PubLinkUtil();
        $citationStruct = $helper->getCitationFromDoiDotOrg($doi);
        if (200 == $citationStruct['status']) {
            $citation = $citationStruct['citation'];
            $publication = new Publication($doi);
            $publication->setCitation($citation);
            $entityHandler->update($publication);
            $entityHandler->persist();
            $entityHandler->flush();
        }
    }

    /**
     * Generate citations for datasets.
     *
     * @Route("/services/citation/dataset/{udi}")
     * @Method("GET")
     *
     * @return Response A Symfony Response instance.
     */
    public function getCitationDatasetAction($udi)
    {
    }

    /**
     * Link Publications to Datasets.
     *
     * @Route("/services/plinker/{udi}/{doi}")
     * @Method("LINK")
     *
     * @return Response A Symfony Response instance.
     */
    public function linkPublicationDatasetAction($udi, $doi)
    {
    }
}
