<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * The index action.
     *
     * @Route("/", name="pelagos_homepage")
     *
     * @return Response A Response instance.
     */
    public function index()
    {
        //if ($this->get('http_kernel')->isDebug()) {
            return $this->render('Default/index.html.twig');
        //} else {
            //return $this->redirect('/', 302);
        //}
        
        // return $this->render('Default/index.html.twig', [
            // 'controller_name' => 'DefaultController',
        // ]);
    }
    
    /**
     * The admin action.
     *
     * @Route("/admin", name="pelagos_admin")
     *
     * @return Response A Response instance.
     */
    public function admin()
    {
        return $this->render('Default/admin.html.twig');
    }

    /**
     * Get the sitemap.xml containing all dataset urls.
     *
     * @return Response
     */
    public function showSiteMapXml()
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
