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
        $facets = $request->query->get('facets');
        $page = $request->query->get('page');
        $numberOfResultsPerPage = $request->query->get('perPage');
        $researchGroupFilter = $request->query->get('researchGroup');
        $fundingOrganizationFilter = $request->query->get('fundingOrg');
        $dataTypeFilter = $request->query->get('dataType');
        $datasetStatusFilter = $request->query->get('status');

        $searchOptions = new SearchOptions($queryString);
        $searchOptions->setCurrentPage($page);
        $searchOptions->setResearchGroupFilter($researchGroupFilter);
        $searchOptions->setFundingOrgFilter($fundingOrganizationFilter);
        $searchOptions->setDataType($dataTypeFilter);
        $searchOptions->setDatasetStatus($datasetStatusFilter);

        $searchOptions->setMaxPerPage($numberOfResultsPerPage);

        $searchOptions->setFacets(array('researchGroup'));

        $searchResults = $multiSearch->search($searchOptions);
        $groups = array(
            'Default',
            'search',
            'result' => array(
                'search',
                'card',
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
                    'card',
                ),
                'doi' => array(
                    'doi',
                ),
                'datasetSubmission' => array(
                    'authors',
                    'coldStorage',
                    'card',
                ),
            ),
        );

        return $jsonSerializer->serialize($searchResults, $groups)->createJsonResponse();
    }
}
