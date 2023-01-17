<?php

namespace App\Controller\Api;

use App\Repository\FunderRepository;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FunderController extends AbstractController
{
    #[Route('/api/funders', name: 'app_api_funders')]
    public function getFunderByName(Request $request, FunderRepository $funderRepository, SerializerInterface $serializer): Response
    {
        $queryString = $request->get('queryString');
        $funders = $funderRepository->findByQuery($queryString);
        /** @param Funder $funders */
        return new JsonResponse($funders, 200);
    }
}
