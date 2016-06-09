<?php

namespace Pelagos\Bundle\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\Controller\Annotations as Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Pelagos\Entity\FundingCycle;

/**
 * The Dataset api controller.
 */
class TreeController extends EntityController
{
    /**
     * Gets a collection of Projects.
     *
     * @param Request $request The request object.
     *
     * @ApiDoc(
     *   section = "Tree",
     *   parameters = {
     *     {"name"="someProperty", "dataType"="string", "required"=false, "description"="Filter by someProperty"}
     *   },
     *   output = "json",
     *   statusCodes = {
     *     200 = "The requested collection of Datasets was successfully retrieved.",
     *     500 = "An internal error has occurred.",
     *   }
     * )
     *
     * @Rest\Get("/json/{type}.json")
     *
     * @Rest\View(serializerEnableMaxDepthChecks = true)
     *
     * @return json
     */
    public function getCollectionAction(Request $request)
    {
        $entities = $this->container->get('pelagos.entity.handler')->getAll(FundingCycle::class);
        
        $tree = json_decode (urldecode($request->query->get('tree')));
        
        //print_r($tree);
        
        return $this->render(
            'PelagosAppBundle:Api:Tree/research_awards.json.twig',
            array(
                'RFPS' => $entities,
                'YR1' => array (
                        'top' => false,
                        'hide' => false,
                        ),
                'tree' => $tree,
                
                )
        );
    }
}
