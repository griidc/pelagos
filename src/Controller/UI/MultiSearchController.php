<?php

namespace App\Controller\UI;

use App\Search\InformationProductSearch;
use App\Search\MultiSearch;
use App\Search\SearchOptions;
use App\Util\JsonSerializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MultiSearchController extends AbstractController
{
    /**
     * @Route("/multi-search")
     */
    public function index(): Response
    {
        return $this->render('MultiSearch/index.html.twig', [
            'controller_name' => 'MultiSearchController',
        ]);
    }

    /**
     * @Route("/api/multi_search", name="app_multi_search_api")
     *
     * @return Response
     */
    public function search(Request $request, MultiSearch $multiSearch, JsonSerializer $jsonSerializer): Response
    {
        $queryString = $request->query->get('queryString');
        $page = $request->query->get('page');
        $researchGroupFilter = $request->query->get('researchGroup');

        $searchOptions = new SearchOptions($queryString);
        $searchOptions->setCurrentPage($page);
        $searchOptions->setResearchGroupFilter($researchGroupFilter);

        $searchOptions->setFacets(array('researchGroup'));

        $searchResults = $multiSearch->search($searchOptions);

        $groups = $groups = array(
            'Default',
            'search',
            'result' => array(
                'search',
                'researchGroup' => array(
                    'search',
                ),
                'researchGroups' => array(
                    'search',
                ),
                'digitalResourceTypeDescriptors' => array(
                    'search',
                ),
                'productTypeDescriptors' => array(
                    'search',
                ),
                'file' => array(
                    'search',
                ),
            ),
        );

        return $jsonSerializer->serialize($searchResults, $groups)->createJsonResponse();
    }
}
