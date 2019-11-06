<?php

namespace Pelagos\Bundle\AppBundle\Controller;

use Doctrine\ORM\Query;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Pelagos\Entity\Dataset;
use Pelagos\Entity\DatasetSubmission;

/**
 * The default controller for the Pelagos App Bundle.
 */
class DefaultController extends Controller
{
    /**
     * The index action.
     *
     * @return Response A Response instance.
     */
    public function indexAction()
    {
        if ($this->get('kernel')->isDebug()) {
            return $this->render('PelagosAppBundle:Default:index.html.twig');
        } else {
            return $this->redirect('/', 302);
        }
    }

    /**
     * The admin action.
     *
     * @return Response A Response instance.
     */
    public function adminAction()
    {
        return $this->render('PelagosAppBundle:Default:admin.html.twig');
    }

    /**
     * Get the sitemap.xml containing all dataset urls.
     *
     * @return Response
     */
    public function showSiteMapXmlAction()
    {
        $container = $this->container;
        $response = new StreamedResponse(function () use ($container) {

            $entityManager = $container->get('doctrine.orm.entity_manager');

            $datasets = $entityManager->getRepository(Dataset::class)->findBy(
                array(
                    'availabilityStatus' =>
                    array(
                        DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE,
                        DatasetSubmission::AVAILABILITY_STATUS_PUBLICLY_AVAILABLE_REMOTELY_HOSTED,
                    )
                )
            );

            echo $this->renderView(
                'PelagosAppBundle:Default:sitemap.xml.twig',
                array(
                    'datasets' => $datasets
                )
            );
        });

        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }
}
