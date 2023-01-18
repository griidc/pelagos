<?php

namespace App\Controller\Api;

use App\Repository\FunderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * This is a Funder API controller class.
 */
class FunderController extends AbstractController
{
    /**
     * READ method for the Funder API controller class.
     *
     * @param  Request          $request          A request object.
     * @param  FunderRepository $funderRepository The FunderRepository class.
     *
     * @return Response A http response object that sends JSON to browser.
     */
    #[Route('/api/funders', name: 'app_api_funders_by_name')]
    public function getFunderByName(Request $request, FunderRepository $funderRepository): Response
    {
        $userQueryString = $request->query->get('queryString');
        $userQueryString = null;
        $funders = $funderRepository->findFunderByPartialName($userQueryString);
        return new JsonResponse($funders);
    }
}
