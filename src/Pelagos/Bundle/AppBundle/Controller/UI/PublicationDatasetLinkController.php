<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Doctrine\Common\Collections\ArrayCollection;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\Publication;
use Pelagos\Entity\PublicationCitation;
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
        $pubLinkUtil = $this->get('pelagos.util.publink');
        $citationStruct = $pubLinkUtil->getCitationFromDoiDotOrg($doi);
        if (200 == $citationStruct['status']) {
            $entityHandler = $this->get('pelagos.entity.handler');

            $publication = new Publication($doi);
            $entityHandler->create($publication);

            $publicationCitation = $citationStruct['citation'];
            $publicationCitation->setPublication($publication);

            $entityHandler->create($publicationCitation);
            return new Response('');
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
