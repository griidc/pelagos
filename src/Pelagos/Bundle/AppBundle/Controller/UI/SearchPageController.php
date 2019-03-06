<?php

namespace Pelagos\Bundle\AppBundle\Controller\UI;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The Dataset Review controller for the Pelagos UI App Bundle.
 *
 * @Route("/search")
 */
class SearchPageController extends UIController
{

    /**
     * The default action for Dataset Review.
     *
     * @Route("")
     *
     * @param Request $request The Symfony request object.
     *
     * @return Response
     */
    public function defaultAction(Request $request)
    {
        $queryTerm = $request->get('query');
        $finder = $this->get('fos_elastica.finder.pelagos.dataset');
        $results = $finder->find($queryTerm);

        return $this->render('PelagosAppBundle:Search:default.html.twig', array(
            'query' => $queryTerm,
            'results' => $results
        ));
    }
}
