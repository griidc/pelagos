<?php

namespace Pelagos\Bundle\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
        $datasets = $this->container->get('pelagos.entity.handler')->getBy(\Pelagos\Entity\Dataset::class, array('udi' => 'A1.x801.000:0021'));
        $dataset = $datasets[0];
        $metadata = $dataset->getMetadata();
        $xml = $metadata->getXml();
        
        $metadata->updateXmlTimeStamp();
        $metadata->addMaintenanceNote('BLAAAAAAAAAAAAAAAA');
        
        $xml =  $xml->asXML(); 
        
        $response = new \Symfony\Component\HttpFoundation\Response($xml);
        $response->headers->set('Content-Type', 'text/xml');
        
        return $response;
        
        return $this->render('PelagosAppBundle:Default:index.html.twig');
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
}
